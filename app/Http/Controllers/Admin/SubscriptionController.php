<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Plan;
use App\Http\Controllers\Controller;
use App\Models\BotUser;
use App\Models\SiteSetting;
use App\Services\Subscriptions\PlanPurchase;
use App\Services\Subscriptions\SubscriptionNotifier;
use App\Services\Subscriptions\SubscriptionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * «Подписки» — лагерь «Кабинеты участниц»: цена подписки, кто на каком тарифе и до какого числа, подарок тарифа на год
 * и ручная выдача на другой срок (оплата переводом, исправление ошибки). Тарифы меняются только через
 * App\Services\Subscriptions\SubscriptionService.
 */
class SubscriptionController extends Controller
{
    /** Фильтры списка: все, без тарифа, по тарифу, заканчиваются в ближайшие 30 дней. */
    private const FILTERS = ['none', 'community', 'active', 'private', 'expiring'];

    public function index(Request $request): View
    {
        $filter = $request->string('filter')->toString();
        $filter = in_array($filter, self::FILTERS, true) ? $filter : '';
        $search = trim($request->string('q')->toString());

        // Все, кто зарегистрировался по почте: тариф можно купить до одобрения анкеты.
        $profiles = BotUser::query()
            ->whereNotNull('email')
            ->when($filter === 'none', fn ($q) => $q->where(fn ($w) => $w
                ->where('plan', Plan::None->value)
                ->orWhereNull('plan_ends_at')
                ->orWhere('plan_ends_at', '<=', now())))
            ->when(in_array($filter, ['community', 'active', 'private'], true), fn ($q) => $q->withPlan(Plan::from($filter))->where('plan', $filter))
            ->when($filter === 'expiring', fn ($q) => $q->where('plan', '!=', Plan::None->value)
                ->where('plan_ends_at', '>', now())
                ->where('plan_ends_at', '<=', now()->addDays(30)))
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('full_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('telegram_username', 'like', "%{$search}%")))
            ->orderByRaw('plan_ends_at is null')
            ->orderBy('plan_ends_at')
            ->orderBy('full_name')
            ->paginate(25)
            ->withQueryString();

        $registered = BotUser::query()->whereNotNull('email');

        return view('admin.subscriptions.index', [
            'profiles' => $profiles,
            'filter' => $filter,
            'search' => $search,
            'prices' => [
                Plan::Community->value => Plan::Community->price(),
                Plan::Active->value => Plan::Active->price(),
                Plan::Private->value => Plan::Private->price(),
            ],
            'priceMax' => SiteSetting::SUBSCRIPTION_PRICE_MAX,
            'closed' => SiteSetting::closedPlans(),
            'managerEmail' => SiteSetting::planRequestEmail(),
            'counts' => [
                '' => (clone $registered)->count(),
                'none' => (clone $registered)->count() - (clone $registered)->withPlan(Plan::Community)->count(),
                'community' => (clone $registered)->withPlan(Plan::Community)->where('plan', Plan::Community->value)->count(),
                'active' => (clone $registered)->withPlan(Plan::Active)->where('plan', Plan::Active->value)->count(),
                'private' => (clone $registered)->withPlan(Plan::Private)->where('plan', Plan::Private->value)->count(),
                'expiring' => (clone $registered)->where('plan', '!=', Plan::None->value)->where('plan_ends_at', '>', now())->where('plan_ends_at', '<=', now()->addDays(30))->count(),
            ],
        ]);
    }

    public function edit(BotUser $profile): View
    {
        return view('admin.subscriptions.edit', [
            'profile' => $profile,
            'current' => $profile->currentPlan(),
            'history' => $profile->subscriptions()->with('payment')->latest('id')->get(),
            'payments' => $profile->payments()->latest('id')->limit(10)->get(),
            'closedPlans' => array_values(array_filter(Plan::paid(), fn (Plan $plan): bool => app(PlanPurchase::class)->isClosed($plan))),
            'unlocked' => collect(Plan::paid())->mapWithKeys(fn (Plan $plan): array => [$plan->value => app(PlanPurchase::class)->unlockedRequest($profile, $plan) !== null]),
        ]);
    }

    /**
     * Цена подписки на год. Меняет цену только для новых платежей: счёт, уже выставленный по старой цене, оплачивается
     * по ней (банк ждёт ту сумму, на которую выставлен счёт), а тарифы, которые уже оплачены, не пересчитываются.
     */
    public function updatePrices(Request $request): RedirectResponse
    {
        $max = SiteSetting::SUBSCRIPTION_PRICE_MAX;

        $data = $request->validate([
            'community' => ['required', 'integer', 'min:1', "max:{$max}"],
            'active' => ['required', 'integer', 'min:1', "max:{$max}", 'gte:community'],
            'private' => ['required', 'integer', 'min:1', "max:{$max}", 'gte:active'],
        ], [
            'active.gte' => __('Active включает всё из Community, поэтому не может стоить дешевле. Проверьте цены.'),
            'private.gte' => __('Private включает всё из Active, поэтому не может стоить дешевле. Проверьте цены.'),
        ], [
            'community' => __('цена Community'),
            'active' => __('цена Active'),
            'private' => __('цена Private'),
        ]);

        $before = [Plan::Community->value => Plan::Community->price(), Plan::Active->value => Plan::Active->price(), Plan::Private->value => Plan::Private->price()];
        $after = ['community' => (int) $data['community'], 'active' => (int) $data['active'], 'private' => (int) $data['private']];

        SiteSetting::setSubscriptionPrices($after);

        Log::info('Цены подписки изменены в админке', ['было' => $before, 'стало' => $after]);

        return redirect()->route('admin.subscriptions.index')
            ->with('success', __('Цены сохранены. Новая цена действует для новых платежей; уже оплаченные подписки не меняются.'));
    }

    /**
     * «Покупка тарифов»: какие тарифы закрыты (вместо «Купить» — «Оставить заявку») и на какую почту писать менеджеру
     * о новых заявках. Закрытие не действует на тех, кому менеджер уже открыл покупку лично.
     */
    public function updatePurchase(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'closed' => ['nullable', 'array'],
            'closed.*' => ['string', Rule::in(array_map(fn (Plan $plan): string => $plan->value, Plan::paid()))],
            'manager_email' => ['nullable', 'string', 'email', 'max:191'],
        ], [
            'manager_email.email' => __('Проверьте адрес почты менеджера.'),
        ], [
            'manager_email' => __('почта менеджера'),
        ]);

        $closed = array_values(array_unique($data['closed'] ?? []));

        SiteSetting::setClosedPlans($closed);
        SiteSetting::setPlanRequestEmail((string) ($data['manager_email'] ?? ''));

        Log::info('Покупка тарифов изменена в админке', ['закрыты' => $closed, 'почта менеджера' => $data['manager_email'] ?? '']);

        $notice = __('Сохранено.');

        if ($closed !== [] && blank($data['manager_email'] ?? null)) {
            $notice .= ' '.__('Почта менеджера не указана: заявки будут видны только в разделе «Заявки на тариф», письма не придут.');
        }

        return redirect()->route('admin.subscriptions.index')->with('success', $notice);
    }

    /** Открыть участнице покупку закрытого тарифа без заявки (например, она позвонила сама). */
    public function unlock(Request $request, BotUser $profile, PlanPurchase $purchase): RedirectResponse
    {
        $data = $request->validate([
            'plan' => ['required', Rule::in(array_map(fn (Plan $plan): string => $plan->value, Plan::paid()))],
        ], [], ['plan' => __('тариф')]);

        $plan = Plan::from($data['plan']);
        $purchase->unlockFor($profile, $plan, $request->boolean('notify'));

        return redirect()->route('admin.subscriptions.edit', $profile)
            ->with('success', __('Покупка тарифа :plan открыта: у участницы появилась кнопка «Купить».', ['plan' => $plan->shortTitle()]));
    }

    /**
     * Подарок: тариф на год включается молча. Участница ничего не получает — ни сообщения в Telegram, ни письма:
     * в кабинете просто открывается доступ. Дальше работают обычные напоминания об окончании срока.
     */
    public function gift(Request $request, BotUser $profile, SubscriptionService $subscriptions): RedirectResponse
    {
        $data = $request->validate([
            'plan' => ['required', Rule::in(array_map(fn (Plan $plan): string => $plan->value, Plan::paid()))],
            'note' => ['nullable', 'string', 'max:200'],
        ], [], [
            'plan' => __('тариф'),
            'note' => __('заметка'),
        ]);

        abort_unless($profile->isApproved(), 422, __('Тариф можно подарить только одобренной участнице.'));

        $plan = Plan::from($data['plan']);
        $note = trim((string) ($data['note'] ?? '')) ?: __('Подарок');

        $subscription = $subscriptions->grant($profile, $plan, $plan->months(), note: $note);

        return redirect()->route('admin.subscriptions.edit', $profile)
            ->with('success', __('Подарок оформлен: :plan включён до :date. Участница об этом не уведомляется.', ['plan' => $plan->shortTitle(), 'date' => $subscription->ends_at->format('d.m.Y')]));
    }

    /** Выдать тариф: на $months месяцев (добавляется к текущему сроку того же тарифа) или до точной даты. */
    public function grant(Request $request, BotUser $profile, SubscriptionService $subscriptions, SubscriptionNotifier $notifier): RedirectResponse
    {
        $data = $request->validate([
            'plan' => ['required', Rule::in(array_map(fn (Plan $plan): string => $plan->value, Plan::paid()))],
            'mode' => ['required', Rule::in(['months', 'until'])],
            'months' => ['nullable', 'required_if:mode,months', 'integer', 'min:1', 'max:60'],
            'until' => ['nullable', 'required_if:mode,until', 'date', 'after:today'],
            'note' => ['nullable', 'string', 'max:200'],
            'notify' => ['nullable', 'boolean'],
        ], [], [
            'plan' => __('тариф'),
            'months' => __('срок в месяцах'),
            'until' => __('дата окончания'),
            'note' => __('заметка'),
        ]);

        abort_unless($profile->isApproved(), 422, __('Тариф можно выдать только одобренной участнице.'));

        $plan = Plan::from($data['plan']);
        $note = trim((string) ($data['note'] ?? '')) ?: __('Выдано в админке');

        $subscription = $data['mode'] === 'until'
            ? $subscriptions->setUntil($profile, $plan, Carbon::parse($data['until'])->endOfDay(), $note)
            : $subscriptions->grant($profile, $plan, (int) $data['months'], note: $note);

        // Сообщение участнице уходит только по галочке: по умолчанию тариф включается молча.
        if ($request->boolean('notify')) {
            $notifier->activated($subscription);
        }

        return redirect()->route('admin.subscriptions.edit', $profile)
            ->with('success', __('Тариф :plan включён до :date.', ['plan' => $plan->shortTitle(), 'date' => $subscription->ends_at->format('d.m.Y')]));
    }

    /** Закрыть платный доступ немедленно: тарифа больше нет. Оплаченные деньги этим не возвращаются: возврат делается в банке. */
    public function revoke(BotUser $profile, SubscriptionService $subscriptions): RedirectResponse
    {
        $subscriptions->revoke($profile);

        return redirect()->route('admin.subscriptions.edit', $profile)
            ->with('success', __('Платный доступ закрыт: у участницы больше нет тарифа.'));
    }
}
