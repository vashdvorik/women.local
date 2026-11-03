<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Plan;
use App\Models\BotUser;
use App\Models\Broadcast;
use App\Models\BroadcastClick;
use App\Models\BroadcastDelivery;
use App\Models\ManagerMessage;
use App\Models\Opportunity;
use App\Models\Payment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Отчёты об активности для страницы «Статистика»: что делают участницы в кабинете (по таблице cabinet_activity, которую
 * наполняет App\Services\Activity\CabinetActivity) и как работают рассылки в Telegram (доставлено, блок, клики по кнопкам).
 * Учёт идёт с момента включения, поэтому в отчёте всегда видно, с какого дня считаются цифры. «Прочитано» Telegram боту не
 * сообщает, поэтому такой цифры здесь нет.
 */
final class ActivityReport
{
    /** Периоды отчёта об инструментах: ключ в адресе → сколько дней (null — за всё время). */
    public const PERIODS = ['30' => 30, '90' => 90, '365' => 365, 'all' => null];

    /** Сколько дней без заходов считаем «перестала заходить». */
    private const INACTIVE_DAYS = 30;

    /** Разделы кабинета: ключ → подпись. */
    public static function sectionLabel(string $key): string
    {
        return match ($key) {
            'home' => __('Главная кабинета'),
            'matches' => __('Рекомендации'),
            'search' => __('Поиск контактов'),
            'people' => __('Профили платформы (список)'),
            'people_profile' => __('Открытые профили участниц'),
            'assistant' => __('AI-помощник'),
            'manager' => __('AI-менеджер'),
            'possibilities' => __('Возможности'),
            'knowledge' => __('Обучение'),
            'initiatives' => __('Инициативы'),
            'profile' => __('Мой профиль'),
            'subscription' => __('Подписка'),
            default => $key,
        };
    }

    /** Действия участниц: ключ → подпись. */
    public static function actionLabel(string $key): string
    {
        return match ($key) {
            'assistant_question' => __('Вопросы AI-помощнику'),
            'manager_question' => __('Вопросы AI-менеджеру'),
            'search_query' => __('Поиски с запросом'),
            'initiative_created' => __('Опубликовано инициатив'),
            'profile_updated' => __('Правки профиля'),
            'checkout_started' => __('Начато оформление пакета'),
            'plan_request' => __('Заявки на пакет'),
            'telegram_connect' => __('Подключения Telegram'),
            default => $key,
        };
    }

    /** @var list<string> */
    private const SECTIONS = ['home', 'matches', 'search', 'people', 'people_profile', 'assistant', 'manager', 'possibilities', 'knowledge', 'initiatives', 'profile', 'subscription'];

    /** @var list<string> */
    private const ACTIONS = ['assistant_question', 'manager_question', 'search_query', 'initiative_created', 'profile_updated', 'checkout_started', 'plan_request', 'telegram_connect'];

    /**
     * Активность в кабинете: заходы по месяцам, ключевые цифры, инструменты, пакеты, ушедшие участницы, история по месяцам.
     *
     * @return array<string, mixed>
     */
    public function activity(string $period): array
    {
        $period = array_key_exists($period, self::PERIODS) ? $period : '30';
        $days = self::PERIODS[$period];
        $today = Carbon::today();

        $since = DB::table('cabinet_activity')->min('day');
        $trackingDays = $since === null ? 0 : (int) Carbon::parse($since)->diffInDays($today) + 1;

        // Пары «участница + день» за последний год: из них считаются месяцы и ключевые цифры
        $yearStart = $today->copy()->subMonths(11)->startOfMonth();
        $pairs = DB::table('cabinet_activity')->where('day', '>=', $yearStart->toDateString())
            ->select('bot_user_id', 'day')->groupBy('bot_user_id', 'day')->get();

        $months = $this->monthBuckets(11, ['users' => [], 'visits' => 0]);

        foreach ($pairs as $pair) {
            $key = substr((string) $pair->day, 0, 7);

            if (isset($months[$key])) {
                $months[$key]['users'][$pair->bot_user_id] = true;
                $months[$key]['visits']++;
            }
        }

        $monthRows = array_map(fn (array $m): array => ['key' => $m['key'], 'label' => $m['label'], 'users' => count($m['users']), 'visits' => $m['visits']], array_values($months));

        $in = fn (int $n) => $pairs->filter(fn ($p): bool => (string) $p->day >= $today->copy()->subDays($n - 1)->toDateString());
        $last7 = $in(7);
        $last30 = $in(30);

        $paid = BotUser::withPlan(Plan::Community)->count();
        $paidActive30 = BotUser::withPlan(Plan::Community)->whereIn('bot_users.id', $last30->pluck('bot_user_id')->unique()->all())->count();

        return [
            'period' => $period,
            'periods' => array_keys(self::PERIODS),
            'trackingSince' => $since === null ? null : Carbon::parse($since)->format('d.m.Y'),
            'trackingDays' => $trackingDays,
            'months' => $monthRows,
            'maxMonthUsers' => max(1, ...array_column($monthRows, 'users')),
            'maxMonthVisits' => max(1, ...array_column($monthRows, 'visits')),
            'active7' => $last7->pluck('bot_user_id')->unique()->count(),
            'active30' => $last30->pluck('bot_user_id')->unique()->count(),
            'visits30' => $last30->count(),
            'paidMembers' => $paid,
            'paidActive30' => $paidActive30,
            'activeShare' => $paid > 0 ? (int) round($paidActive30 / $paid * 100) : 0,
            'daysPerActive30' => $last30->pluck('bot_user_id')->unique()->count() > 0
                ? round($last30->count() / $last30->pluck('bot_user_id')->unique()->count(), 1) : 0,
            'sections' => $this->usage(self::SECTIONS, $days, true),
            'actions' => $this->usage(self::ACTIONS, $days, false),
            'plans' => $this->plansActive30($today),
            'inactive' => $trackingDays >= self::INACTIVE_DAYS ? $this->inactive($today) : null,
            'inactiveDays' => self::INACTIVE_DAYS,
            'history' => $this->history(),
        ];
    }

    /**
     * Использование разделов или действий за период: сколько участниц и сколько раз.
     *
     * @param  list<string>  $keys
     * @return list<array{key: string, label: string, users: int, hits: int, share: int}>
     */
    private function usage(array $keys, ?int $days, bool $section): array
    {
        $from = $days === null ? null : Carbon::today()->subDays($days - 1)->toDateString();

        $totalUsers = (int) DB::table('cabinet_activity')->when($from, fn ($q) => $q->where('day', '>=', $from))->distinct()->count('bot_user_id');

        $rows = DB::table('cabinet_activity')
            ->when($from, fn ($q) => $q->where('day', '>=', $from))
            ->whereIn('key', $keys)
            ->select('key')
            ->selectRaw('count(distinct bot_user_id) as users, sum(hits) as hits')
            ->groupBy('key')
            ->get()
            ->keyBy('key');

        return collect($keys)->map(function (string $key) use ($rows, $totalUsers, $section): array {
            $row = $rows->get($key);
            $users = (int) ($row->users ?? 0);

            return [
                'key' => $key,
                'label' => $section ? self::sectionLabel($key) : self::actionLabel($key),
                'users' => $users,
                'hits' => (int) ($row->hits ?? 0),
                'share' => $totalUsers > 0 ? (int) round($users / $totalUsers * 100) : 0,
            ];
        })->sortByDesc('hits')->values()->all();
    }

    /**
     * Сколько участниц заходило за 30 дней на каждом пакете (пакет на сегодня).
     *
     * @return list<array{plan: string, title: string, users: int}>
     */
    private function plansActive30(Carbon $today): array
    {
        $rows = DB::table('cabinet_activity as a')
            ->join('bot_users as u', 'u.id', '=', 'a.bot_user_id')
            ->where('a.day', '>=', $today->copy()->subDays(29)->toDateString())
            ->where('u.plan_ends_at', '>', now())
            ->select('u.plan')
            ->selectRaw('count(distinct a.bot_user_id) as users')
            ->groupBy('u.plan')
            ->pluck('users', 'plan');

        return collect(Plan::paid())->map(fn (Plan $plan): array => [
            'plan' => $plan->value,
            'title' => $plan->shortTitle(),
            'users' => (int) ($rows[$plan->value] ?? 0),
        ])->all();
    }

    /**
     * Участницы с действующим пакетом, которые давно не заходили: их стоит позвать обратно. Показывается, когда учёт идёт
     * не меньше этого срока: иначе «не заходили» значило бы просто «учёт ещё не начался».
     *
     * @return array{total: int, rows: list<array{id: int, name: string, plan: string, last: ?string}>}
     */
    private function inactive(Carbon $today): array
    {
        $limit = $today->copy()->subDays(self::INACTIVE_DAYS - 1)->toDateString();

        $query = BotUser::withPlan(Plan::Community)
            ->whereNotIn('bot_users.id', DB::table('cabinet_activity')->where('day', '>=', $limit)->select('bot_user_id'));

        return [
            'total' => (clone $query)->count(),
            'rows' => (clone $query)->orderByRaw('last_seen_at is null')->orderBy('last_seen_at')->limit(15)
                ->get(['id', 'full_name', 'first_name', 'email', 'plan', 'plan_ends_at', 'last_seen_at'])
                ->map(fn (BotUser $u): array => [
                    'id' => $u->id,
                    'name' => $u->full_name ?: ($u->first_name ?: $u->email),
                    'plan' => $u->currentPlan()->shortTitle(),
                    'last' => $u->last_seen_at?->format('d.m.Y'),
                ])->all(),
        ];
    }

    /**
     * История по месяцам из того, что уже лежит в базе (до включения учёта): новые участницы, оплаты, вопросы AI-менеджеру,
     * опубликованные инициативы.
     *
     * @return array{rows: list<array<string, mixed>>, max: array<string, int>}
     */
    private function history(): array
    {
        $months = $this->monthBuckets(11, ['registrations' => 0, 'payments' => 0, 'revenue' => 0, 'questions' => 0, 'initiatives' => 0]);
        $from = Carbon::today()->subMonths(11)->startOfMonth();

        BotUser::query()->where('created_at', '>=', $from)->get(['created_at'])
            ->each(function ($row) use (&$months): void {
                $k = $row->created_at?->format('Y-m');
                isset($months[$k]) && $months[$k]['registrations']++;
            });

        Payment::query()->where('status', Payment::STATUS_PAID)->where('is_test', false)->where('paid_at', '>=', $from)->get(['paid_at', 'amount'])
            ->each(function ($row) use (&$months): void {
                $k = $row->paid_at?->format('Y-m');

                if (isset($months[$k])) {
                    $months[$k]['payments']++;
                    $months[$k]['revenue'] += (int) round($row->amount / 100);
                }
            });

        ManagerMessage::query()->where('role', 'user')->where('created_at', '>=', $from)->get(['created_at'])
            ->each(function ($row) use (&$months): void {
                $k = $row->created_at?->format('Y-m');
                isset($months[$k]) && $months[$k]['questions']++;
            });

        Opportunity::approved()->where('created_at', '>=', $from)->get(['created_at'])
            ->each(function ($row) use (&$months): void {
                $k = $row->created_at?->format('Y-m');
                isset($months[$k]) && $months[$k]['initiatives']++;
            });

        $rows = array_values($months);

        return [
            'rows' => $rows,
            'max' => [
                'registrations' => max(1, ...array_column($rows, 'registrations')),
                'payments' => max(1, ...array_column($rows, 'payments')),
                'questions' => max(1, ...array_column($rows, 'questions')),
                'initiatives' => max(1, ...array_column($rows, 'initiatives')),
            ],
        ];
    }

    /**
     * Рассылки в Telegram: последние отправленные с итогами и кликами по кнопкам, плюс сводка за 30 дней.
     *
     * @return array<string, mixed>
     */
    public function broadcasts(): array
    {
        $broadcasts = Broadcast::query()
            ->whereIn('status', [Broadcast::STATUS_SENDING, Broadcast::STATUS_SENT])
            ->orderByDesc('id')->limit(20)->get();

        $ids = $broadcasts->pluck('id')->all();

        // клики по кнопкам: сколько нажатий и сколько разных получательниц
        $buttonStats = BroadcastClick::query()->whereIn('broadcast_id', $ids)
            ->selectRaw('broadcast_id, button, count(*) as clicks, count(distinct broadcast_delivery_id) as people')
            ->groupBy('broadcast_id', 'button')->get()->groupBy('broadcast_id');

        $clickers = BroadcastDelivery::query()->whereIn('broadcast_id', $ids)->where('clicks', '>', 0)
            ->selectRaw('broadcast_id, count(*) as people')->groupBy('broadcast_id')->pluck('people', 'broadcast_id');

        $rows = $broadcasts->map(function (Broadcast $b) use ($buttonStats, $clickers): array {
            $first = trim((string) strtok(strip_tags((string) ($b->texts['ru'] ?? '')), "\n"));
            $delivered = (int) $b->sent;
            $people = (int) ($clickers[$b->id] ?? 0);

            return [
                'id' => $b->id,
                'title' => Str::limit($first !== '' ? $first : __('Без текста'), 70),
                'date' => ($b->started_at ?? $b->created_at)?->format('d.m.Y H:i'),
                'audience' => $b->audienceLabel(),
                'total' => (int) $b->total,
                'delivered' => $delivered,
                'blocked' => (int) $b->blocked,
                'failed' => (int) $b->failed,
                'clickers' => $people,
                'ctr' => $delivered > 0 ? round($people / $delivered * 100, 1) : 0,
                'buttons' => collect($buttonStats->get($b->id, []))->map(fn ($row): array => [
                    'label' => (string) ($b->buttons[(int) $row->button]['label']['ru'] ?? __('Кнопка :n', ['n' => (int) $row->button + 1])),
                    'clicks' => (int) $row->clicks,
                    'people' => (int) $row->people,
                ])->sortBy('label')->values()->all(),
                'trackable' => filled($b->buttons),
            ];
        })->all();

        $since = now()->subDays(30);
        $recent = Broadcast::query()->whereIn('status', [Broadcast::STATUS_SENDING, Broadcast::STATUS_SENT])->where('created_at', '>=', $since);

        return [
            'rows' => $rows,
            'count30' => (clone $recent)->count(),
            'sent30' => (int) (clone $recent)->sum('sent'),
            'blocked30' => (int) (clone $recent)->sum('blocked'),
            'clicks30' => BroadcastClick::query()->where('clicked_at', '>=', $since)->count(),
            'connected' => BotUser::withPlan(Plan::Community)->whereNotNull('telegram_id')->count(),
            'paid' => BotUser::withPlan(Plan::Community)->count(),
        ];
    }

    /**
     * Активность одной участницы для её карточки в админке.
     *
     * @return array{lastSeen: ?string, days30: int, daysTotal: int, firstSeen: ?string, usage: list<array{label: string, hits: int, days: int}>, clicks: int}
     */
    public function profile(BotUser $user): array
    {
        $from30 = Carbon::today()->subDays(29)->toDateString();
        $base = DB::table('cabinet_activity')->where('bot_user_id', $user->getKey());

        $usage = (clone $base)->select('key')->selectRaw('sum(hits) as hits, count(*) as days')->groupBy('key')->orderByDesc('hits')->get()
            ->map(fn ($row): array => [
                'label' => in_array($row->key, self::SECTIONS, true) ? self::sectionLabel($row->key) : self::actionLabel($row->key),
                'hits' => (int) $row->hits,
                'days' => (int) $row->days,
            ])->all();

        return [
            'lastSeen' => $user->last_seen_at?->format('d.m.Y H:i'),
            'firstSeen' => ($first = (clone $base)->min('day')) === null ? null : Carbon::parse($first)->format('d.m.Y'),
            'days30' => (int) (clone $base)->where('day', '>=', $from30)->distinct()->count('day'),
            'daysTotal' => (int) (clone $base)->distinct()->count('day'),
            'usage' => $usage,
            'clicks' => BroadcastClick::query()->where('bot_user_id', $user->getKey())->count(),
        ];
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, array<string, mixed>>
     */
    private function monthBuckets(int $monthsBack, array $values): array
    {
        $months = [];

        for ($i = $monthsBack; $i >= 0; $i--) {
            $date = Carbon::today()->startOfMonth()->subMonths($i);
            $months[$date->format('Y-m')] = ['key' => $date->format('Y-m'), 'label' => $this->monthLabel((int) $date->format('n')).' '.$date->format('y'), ...$values];
        }

        return $months;
    }

    private function monthLabel(int $month): string
    {
        return [
            1 => __('Янв'), 2 => __('Фев'), 3 => __('Мар'), 4 => __('Апр'), 5 => __('Май'), 6 => __('Июн'),
            7 => __('Июл'), 8 => __('Авг'), 9 => __('Сен'), 10 => __('Окт'), 11 => __('Ноя'), 12 => __('Дек'),
        ][$month] ?? '';
    }
}
