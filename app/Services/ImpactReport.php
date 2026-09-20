<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BotUser;
use App\Models\LoginToken;
use App\Models\Opportunity;

/**
 * Отчёт «Статистика платформы»: живой срез по составу сообщества, готовности профилей
 * и активности участниц. Один расчёт кормит и страницу админки, и PDF-выгрузку, поэтому
 * цифры в них всегда совпадают. Раньше жил в Filament-странице ImpactMetrics.
 *
 * Посты участниц считаются только одобренные: неодобренное не является публикацией.
 */
class ImpactReport
{
    /**
     * @return array<string, mixed>
     */
    public function viewData(): array
    {
        $data = $this->metrics();

        return [
            ...$data,
            ...$this->presentation($data),
            // Замыкания, а не вызовы через $this: PDF рендерит те же данные обычным Blade-шаблоном.
            'formatNumber' => fn (int|float $value): string => $this->formatNumber($value),
            'safePercent' => fn (int|float $value): int => $this->safePercent($value),
        ];
    }

    public function formatNumber(int|float $value): string
    {
        return number_format((float) $value, 0, ',', ' ');
    }

    public function safePercent(int|float $value): int
    {
        return max(0, min(100, (int) round($value)));
    }

    /**
     * @return array<string, mixed>
     */
    private function metrics(): array
    {
        $totalApplications = BotUser::count();
        $approvedCount = BotUser::approved()->count();
        $pendingCount = BotUser::pending()->count();
        $rejectedCount = BotUser::rejected()->count();

        $withBusinessDescription = BotUser::approved()->whereNotNull('description')->where('description', '!=', '')->count();
        $withExpectations = BotUser::approved()->whereNotNull('expectation')->where('expectation', '!=', '')->count();

        $completeProfiles = BotUser::approved()
            ->whereNotNull('description')->where('description', '!=', '')
            ->whereNotNull('expectation')->where('expectation', '!=', '')
            ->count();

        $withEmbedding = BotUser::approved()->whereNotNull('embedding')->count();
        $withAvatar = BotUser::approved()->whereNotNull('avatar_path')->count();
        $withUsername = BotUser::approved()->whereNotNull('telegram_username')->count();

        $opportunitiesTotal = Opportunity::approved()->count();
        $opportunitiesLast30 = Opportunity::approved()->where('created_at', '>=', now()->subDays(30))->count();
        $opportunityAuthors = Opportunity::approved()->distinct('bot_user_id')->count('bot_user_id');

        $opportunitiesByType = Opportunity::approved()
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();

        $loginTokensIssued = LoginToken::count();
        $loginTokensIssuedLast30 = LoginToken::where('created_at', '>=', now()->subDays(30))->count();
        $loginTokensUsed = LoginToken::whereNotNull('used_at')->count();
        $activeCabinetUsers = LoginToken::whereNotNull('used_at')->distinct('telegram_id')->count('telegram_id');

        $newApplicationsLast30 = BotUser::where('created_at', '>=', now()->subDays(30))->count();
        $approvedLast30 = BotUser::approved()->where('approved_at', '>=', now()->subDays(30))->count();

        $approvalRate = $this->percent($approvedCount, $totalApplications);
        $profileCompletionRate = $this->percent($completeProfiles, $approvedCount);
        $aiReadinessRate = $this->percent($withEmbedding, $approvedCount);
        $publicationActivationRate = $this->percent($opportunityAuthors, $approvedCount);
        $cabinetActivationRate = $this->percent($activeCabinetUsers, $approvedCount);

        $platformReadiness = (int) round(collect([
            $profileCompletionRate,
            $aiReadinessRate,
            $publicationActivationRate,
            $cabinetActivationRate,
        ])->average() ?? 0);

        return [
            'totalApplications' => $totalApplications,
            'approvedCount' => $approvedCount,
            'pendingCount' => $pendingCount,
            'rejectedCount' => $rejectedCount,
            'withBusinessDescription' => $withBusinessDescription,
            'withExpectations' => $withExpectations,
            'completeProfiles' => $completeProfiles,
            'withEmbedding' => $withEmbedding,
            'withAvatar' => $withAvatar,
            'withUsername' => $withUsername,
            'opportunitiesTotal' => $opportunitiesTotal,
            'opportunitiesLast30' => $opportunitiesLast30,
            'opportunityAuthors' => $opportunityAuthors,
            'opportunitiesByType' => $opportunitiesByType,
            'loginTokensIssued' => $loginTokensIssued,
            'loginTokensIssuedLast30' => $loginTokensIssuedLast30,
            'loginTokensUsed' => $loginTokensUsed,
            'activeCabinetUsers' => $activeCabinetUsers,
            'newApplicationsLast30' => $newApplicationsLast30,
            'approvedLast30' => $approvedLast30,
            'approvalRate' => $approvalRate,
            'profileCompletionRate' => $profileCompletionRate,
            'aiReadinessRate' => $aiReadinessRate,
            'publicationActivationRate' => $publicationActivationRate,
            'cabinetActivationRate' => $cabinetActivationRate,
            'platformReadiness' => $platformReadiness,
            'registrationChart' => $this->memberChart(),
            'opportunityChart' => $this->opportunityChart(),
            'latestMembers' => BotUser::approved()
                ->latest('approved_at')
                ->limit(6)
                ->get(['full_name', 'telegram_username', 'approved_at', 'description']),
            'latestOpportunities' => Opportunity::approved()
                ->with('author')
                ->latest()
                ->limit(5)
                ->get(['id', 'bot_user_id', 'type', 'title', 'created_at']),
            'generatedAt' => now()->format('d.m.Y H:i'),
        ];
    }

    /**
     * Готовые для вывода структуры: строки воронки, полосы качества, типы постов.
     * Уже основной выборки: несколько полей, дублировавших соседние строки другими
     * словами, сознательно не выводятся.
     *
     * @param  array<string, mixed>  $d
     * @return array<string, mixed>
     */
    private function presentation(array $d): array
    {
        $statusRows = [
            ['label' => 'Одобрено', 'value' => $d['approvedCount'], 'percent' => $d['approvalRate'], 'tone' => 'success'],
            ['label' => 'Ожидают решения', 'value' => $d['pendingCount'], 'percent' => $this->percent($d['pendingCount'], $d['totalApplications']), 'tone' => 'warning'],
            ['label' => 'Отклонено', 'value' => $d['rejectedCount'], 'percent' => $this->percent($d['rejectedCount'], $d['totalApplications']), 'tone' => 'error'],
        ];

        $qualityRows = [
            ['label' => 'Заполнили бизнес-профиль', 'caption' => 'Указали описание бизнеса и запрос к сообществу', 'value' => $d['completeProfiles'], 'total' => $d['approvedCount'], 'percent' => $d['profileCompletionRate']],
            ['label' => 'Готовы к AI-рекомендациям', 'caption' => 'Профиль проиндексирован для поиска и подбора контактов', 'value' => $d['withEmbedding'], 'total' => $d['approvedCount'], 'percent' => $d['aiReadinessRate']],
            ['label' => 'Активировали личный кабинет', 'caption' => 'Хотя бы один вход по Telegram-токену', 'value' => $d['activeCabinetUsers'], 'total' => $d['approvedCount'], 'percent' => $d['cabinetActivationRate']],
            ['label' => 'Публиковали возможности', 'caption' => 'Разместили запрос, партнёрство или событие', 'value' => $d['opportunityAuthors'], 'total' => $d['approvedCount'], 'percent' => $d['publicationActivationRate']],
            ['label' => 'Указали Telegram для связи', 'caption' => 'Участницы могут написать напрямую', 'value' => $d['withUsername'], 'total' => $d['approvedCount'], 'percent' => $this->percent($d['withUsername'], $d['approvedCount'])],
            ['label' => 'Добавили фото профиля', 'caption' => 'Профиль с фотографией, а не инициалом', 'value' => $d['withAvatar'], 'total' => $d['approvedCount'], 'percent' => $this->percent($d['withAvatar'], $d['approvedCount'])],
        ];

        $typeMeta = [
            'project' => ['label' => 'Запросы', 'icon' => '💼'],
            'meeting' => ['label' => 'Партнёрства', 'icon' => '🤝'],
            'event' => ['label' => 'События', 'icon' => '📅'],
        ];

        $opportunitiesTotal = $d['opportunitiesTotal'];
        $opportunitiesByType = $d['opportunitiesByType'];

        $opportunityTypeRows = collect($typeMeta)->map(function (array $meta, string $type) use ($opportunitiesByType, $opportunitiesTotal): array {
            $count = (int) ($opportunitiesByType[$type] ?? 0);

            return [
                ...$meta,
                'type' => $type,
                'count' => $count,
                'percent' => $this->percent($count, $opportunitiesTotal),
            ];
        })->values()->all();

        return [
            'statusRows' => $statusRows,
            'qualityRows' => $qualityRows,
            'typeMeta' => $typeMeta,
            'opportunityTypeRows' => $opportunityTypeRows,
            'maxMemberChart' => max(1, (int) collect($d['registrationChart'])->max('applications'), (int) collect($d['registrationChart'])->max('approved')),
            'maxOpportunityChart' => max(1, (int) collect($d['opportunityChart'])->max('opportunities')),
        ];
    }

    private function percent(int $value, int $total): int
    {
        return $total <= 0 ? 0 : (int) round($value / $total * 100);
    }

    /** @return array<int, array{key: string, label: string, applications: int, approved: int}> */
    private function memberChart(): array
    {
        $months = $this->emptyMonthBuckets(11, ['applications' => 0, 'approved' => 0]);

        BotUser::query()
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->get(['created_at', 'approved_at', 'status'])
            ->each(function (BotUser $user) use (&$months): void {
                $createdKey = $user->created_at?->format('Y-m');

                if ($createdKey && isset($months[$createdKey])) {
                    $months[$createdKey]['applications']++;
                }

                $approvedKey = $user->approved_at?->format('Y-m');

                if ($user->isApproved() && $approvedKey && isset($months[$approvedKey])) {
                    $months[$approvedKey]['approved']++;
                }
            });

        return array_values($months);
    }

    /** @return array<int, array{key: string, label: string, opportunities: int}> */
    private function opportunityChart(): array
    {
        $months = $this->emptyMonthBuckets(5, ['opportunities' => 0]);

        Opportunity::approved()
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->get(['created_at'])
            ->each(function (Opportunity $opportunity) use (&$months): void {
                $key = $opportunity->created_at?->format('Y-m');

                if ($key && isset($months[$key])) {
                    $months[$key]['opportunities']++;
                }
            });

        return array_values($months);
    }

    /**
     * @param  array<string, int>  $values
     * @return array<string, array<string, int|string>>
     */
    private function emptyMonthBuckets(int $monthsBack, array $values): array
    {
        $months = [];

        for ($i = $monthsBack; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $key = $date->format('Y-m');

            $months[$key] = [
                'key' => $key,
                'label' => $this->monthLabel((int) $date->format('n')).' '.$date->format('y'),
                ...$values,
            ];
        }

        return $months;
    }

    private function monthLabel(int $month): string
    {
        return [
            1 => 'Янв', 2 => 'Фев', 3 => 'Мар', 4 => 'Апр', 5 => 'Май', 6 => 'Июн',
            7 => 'Июл', 8 => 'Авг', 9 => 'Сен', 10 => 'Окт', 11 => 'Ноя', 12 => 'Дек',
        ][$month] ?? '';
    }
}
