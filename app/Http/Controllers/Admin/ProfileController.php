<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileRequest;
use App\Enums\Plan;
use App\Models\BotUser;
use App\Models\Payment;
use App\Services\ProfileModeration;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Модерация профилей участниц из Telegram-бота (раньше — Filament BotUserResource).
 */
class ProfileController extends Controller
{
    private const STATUSES = [
        BotUser::STATUS_PENDING,
        BotUser::STATUS_APPROVED,
        BotUser::STATUS_REJECTED,
    ];

    private const SORTABLE = ['full_name', 'status', 'created_at'];

    /**
     * Участницы списка: только те, кто заполнил анкету (пока человек не прошёл регистрацию до конца, проверять нечего),
     * с фильтрами вкладки и поиска. Список и выгрузка берут их отсюда, чтобы файл совпадал с тем, что на экране.
     */
    private function filteredProfiles(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $status = $request->string('status')->toString();
        $status = in_array($status, self::STATUSES, true) ? $status : '';
        $search = trim($request->string('q')->toString());

        return BotUser::query()
            ->whereNotNull('profile_completed_at')
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->when($request->boolean('hidden'), fn ($q) => $q->where('is_hidden', true))
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('full_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('telegram_username', 'like', "%{$search}%")));
    }

    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $status = in_array($status, self::STATUSES, true) ? $status : '';
        $search = trim($request->string('q')->toString());
        $onlyHidden = $request->boolean('hidden');
        $sort = in_array($request->string('sort')->toString(), self::SORTABLE, true)
            ? $request->string('sort')->toString()
            : 'created_at';
        $dir = $request->string('dir', $sort === 'created_at' ? 'desc' : 'asc')->toString() === 'asc' ? 'asc' : 'desc';

        $profiles = $this->filteredProfiles($request)
            ->orderBy($sort, $dir)
            ->paginate(25)
            ->withQueryString();

        $counts = BotUser::query()->whereNotNull('profile_completed_at')->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.profiles.index', [
            'profiles' => $profiles,
            'status' => $status,
            'search' => $search,
            'counts' => $counts,
            'onlyHidden' => $onlyHidden,
            'hiddenCount' => BotUser::query()->whereNotNull('profile_completed_at')->where('is_hidden', true)->count(),
        ]);
    }

    /**
     * Выгрузка участниц в файл CSV (UTF-8 с BOM, разделитель «;»: Excel открывает без плясок). Берёт тот же список, что на
     * экране, с теми же вкладкой и поиском. Пароли, служебные токены и векторы подбора в файл не попадают.
     */
    public function export(Request $request): StreamedResponse
    {
        $name = 'participants-'.now()->format('Y-m-d').'.csv';
        $yes = __('да');
        $no = __('нет');
        $date = fn ($value, string $format = 'd.m.Y H:i'): string => $value ? $value->format($format) : '';

        return response()->streamDownload(function () use ($request, $yes, $no, $date) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'ID', __('Имя и фамилия'), __('Имя'), __('Почта регистрации'), __('Почта для связи'), __('Телефон'), __('Регион'),
                'Telegram', 'Telegram ID', __('Язык'), __('Статус анкеты'), __('Скрыта от участниц'), __('Тариф'), __('Статус тарифа'),
                __('Тариф действует до'), __('Оплачено, руб.'), __('Чем занимается'), __('Что ищет'), __('Почта подтверждена'),
                __('Вход через Google'), __('Дата регистрации'), __('Анкета заполнена'), __('Одобрена'), __('Первый вход в кабинет'),
            ], ';');

            $this->filteredProfiles($request)
                ->withSum(['payments as paid_total' => fn ($q) => $q->where('status', Payment::STATUS_PAID)->where('is_test', false)], 'amount')
                ->orderBy('id')
                ->chunkById(500, function ($chunk) use ($out, $yes, $no, $date) {
                    foreach ($chunk as $user) {
                        $stored = Plan::fromStored($user->plan);
                        $planStatus = ! $stored->isPaid() ? '' : ($user->currentPlan()->isPaid() ? __('действует') : __('истёк'));

                        fputcsv($out, array_map([self::class, 'safeCell'], [
                            $user->id,
                            $user->full_name,
                            $user->first_name,
                            $user->email,
                            $user->contact_email,
                            $user->phone,
                            $user->region,
                            $user->telegram_username ? '@'.$user->telegram_username : '',
                            $user->telegram_id,
                            $user->locale,
                            match ($user->status) { BotUser::STATUS_APPROVED => __('Одобрена'), BotUser::STATUS_REJECTED => __('Отклонена'), BotUser::STATUS_PENDING => __('Ожидает'), default => (string) $user->status },
                            $user->is_hidden ? $yes : $no,
                            $stored->isPaid() ? $stored->title() : '',
                            $planStatus,
                            $date($user->plan_ends_at, 'd.m.Y'),
                            $user->paid_total ? number_format($user->paid_total / 100, 2, '.', '') : '',
                            $user->description,
                            $user->expectation,
                            $user->email_verified_at ? $yes : $no,
                            $user->google_id ? $yes : $no,
                            $date($user->created_at),
                            $date($user->profile_completed_at),
                            $date($user->approved_at),
                            $date($user->cabinet_opened_at),
                        ]), ';');
                    }
                }, 'bot_users.id', 'id');

            fclose($out);
        }, $name, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Ячейка, которую Excel не примет за формулу: значения, начинающиеся с = + - @ (или табуляции), получают апостроф в начале.
     * Текст в анкете вводит сама участница, поэтому такая защита нужна. Телефон со знаком «+» не трогаем.
     */
    public static function safeCell(mixed $value): mixed
    {
        if (! is_string($value) || $value === '') {
            return $value;
        }

        if (preg_match('/^\+[\d\s().-]{5,}$/', $value) === 1) {
            return $value;
        }

        return in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true) ? "'".$value : $value;
    }

    public function show(BotUser $profile): View
    {
        return view('admin.profiles.show', ['profile' => $profile, 'activity' => app(\App\Services\ActivityReport::class)->profile($profile)]);
    }

    public function edit(BotUser $profile): View
    {
        return view('admin.profiles.edit', ['profile' => $profile]);
    }

    public function update(ProfileRequest $request, BotUser $profile): RedirectResponse
    {
        $profile->update($request->validated());

        return redirect()->route('admin.profiles.show', $profile)->with('success', __('Профиль сохранён.'));
    }

    /**
     * «Скрыть от других участниц» / «Показать»: скрытый человек пользуется платформой изнутри, но не виден в каталоге, поиске,
     * подборе контактов и AI-помощнике и не может публиковать инициативы. Только администратор платформы (маршрут в группе админки).
     */
    public function toggleVisibility(BotUser $profile): RedirectResponse
    {
        $profile->forceFill(['is_hidden' => ! $profile->is_hidden])->save();

        return back()->with('success', $profile->is_hidden
            ? __('Профиль скрыт от других участниц.')
            : __('Профиль снова виден другим участницам.'));
    }

    public function approve(BotUser $profile, ProfileModeration $moderation): RedirectResponse
    {
        $moderation->approve($profile);

        return back()->with('success', __('Профиль участницы одобрен.'));
    }

    public function reject(BotUser $profile, ProfileModeration $moderation): RedirectResponse
    {
        $moderation->reject($profile);

        return back()->with('success', __('Профиль участницы отклонён.'));
    }

    public function destroy(BotUser $profile): RedirectResponse
    {
        $profile->delete();

        return redirect()->route('admin.profiles.index')->with('success', __('Профиль удалён.'));
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ids = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ], [], ['ids' => __('профили')])['ids'];

        $deleted = BotUser::query()->whereIn('id', $ids)->delete();

        return redirect()->route('admin.profiles.index')->with('success', __('Удалено профилей: :count.', ['count' => $deleted]));
    }
}
