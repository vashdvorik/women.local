<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\BotUser;
use App\Models\LoginToken;
use App\Models\Opportunity;
use Filament\Pages\Page;

class ImpactMetrics extends Page
{
    protected string $view = 'filament.pages.impact-metrics';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Статистика и impact';

    protected static ?string $title = 'Статистика платформы';

    protected static ?int $navigationSort = 2;

    /**
     * @return array<string, mixed>
     */
    public function getViewData(): array
    {
        $totalApplications = BotUser::count();
        $approvedCount     = BotUser::approved()->count();
        $pendingCount      = BotUser::pending()->count();
        $rejectedCount     = BotUser::rejected()->count();

        $withBusinessDescription = BotUser::approved()
            ->whereNotNull('description')
            ->where('description', '!=', '')
            ->count();

        $withExpectations = BotUser::approved()
            ->whereNotNull('expectation')
            ->where('expectation', '!=', '')
            ->count();

        $completeProfiles = BotUser::approved()
            ->whereNotNull('description')
            ->where('description', '!=', '')
            ->whereNotNull('expectation')
            ->where('expectation', '!=', '')
            ->count();

        $withEmbedding = BotUser::approved()->whereNotNull('embedding')->count();
        $withAvatar    = BotUser::approved()->whereNotNull('avatar_path')->count();
        $withUsername  = BotUser::approved()->whereNotNull('telegram_username')->count();

        $opportunitiesTotal  = Opportunity::count();
        $opportunitiesLast30 = Opportunity::where('created_at', '>=', now()->subDays(30))->count();
        $opportunityAuthors  = Opportunity::query()->distinct('bot_user_id')->count('bot_user_id');

        $opportunitiesByType = Opportunity::query()
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();

        $loginTokensIssued       = LoginToken::count();
        $loginTokensIssuedLast30 = LoginToken::where('created_at', '>=', now()->subDays(30))->count();
        $loginTokensUsed         = LoginToken::whereNotNull('used_at')->count();
        $activeCabinetUsers      = LoginToken::whereNotNull('used_at')->distinct('telegram_id')->count('telegram_id');

        $newApplicationsLast30 = BotUser::where('created_at', '>=', now()->subDays(30))->count();
        $approvedLast30        = BotUser::approved()->where('approved_at', '>=', now()->subDays(30))->count();

        $approvalRate              = $this->percent($approvedCount, $totalApplications);
        $profileCompletionRate     = $this->percent($completeProfiles, $approvedCount);
        $aiReadinessRate           = $this->percent($withEmbedding, $approvedCount);
        $publicationActivationRate = $this->percent($opportunityAuthors, $approvedCount);
        $cabinetActivationRate     = $this->percent($activeCabinetUsers, $approvedCount);

        $platformReadiness = (int) round(collect([
            $profileCompletionRate,
            $aiReadinessRate,
            $publicationActivationRate,
            $cabinetActivationRate,
        ])->average() ?? 0);

        $latestMembers = BotUser::approved()
            ->latest('approved_at')
            ->limit(6)
            ->get(['full_name', 'telegram_username', 'approved_at', 'description']);

        $latestOpportunities = Opportunity::with('author')
            ->latest()
            ->limit(5)
            ->get(['id', 'bot_user_id', 'type', 'title', 'created_at']);

        return [
            'totalApplications'         => $totalApplications,
            'approvedCount'             => $approvedCount,
            'pendingCount'              => $pendingCount,
            'rejectedCount'             => $rejectedCount,
            'withBusinessDescription'   => $withBusinessDescription,
            'withExpectations'          => $withExpectations,
            'completeProfiles'          => $completeProfiles,
            'withEmbedding'             => $withEmbedding,
            'withAvatar'                => $withAvatar,
            'withUsername'              => $withUsername,
            'opportunitiesTotal'        => $opportunitiesTotal,
            'opportunitiesLast30'       => $opportunitiesLast30,
            'opportunityAuthors'        => $opportunityAuthors,
            'opportunitiesByType'       => $opportunitiesByType,
            'loginTokensIssued'         => $loginTokensIssued,
            'loginTokensIssuedLast30'   => $loginTokensIssuedLast30,
            'loginTokensUsed'           => $loginTokensUsed,
            'activeCabinetUsers'        => $activeCabinetUsers,
            'newApplicationsLast30'     => $newApplicationsLast30,
            'approvedLast30'            => $approvedLast30,
            'approvalRate'              => $approvalRate,
            'profileCompletionRate'     => $profileCompletionRate,
            'aiReadinessRate'           => $aiReadinessRate,
            'publicationActivationRate' => $publicationActivationRate,
            'cabinetActivationRate'     => $cabinetActivationRate,
            'platformReadiness'         => $platformReadiness,
            'registrationChart'         => $this->buildMemberChart(),
            'opportunityChart'          => $this->buildOpportunityChart(),
            'latestMembers'             => $latestMembers,
            'latestOpportunities'       => $latestOpportunities,
            'generatedAt'               => now()->format('d.m.Y H:i'),
        ];
    }

    private function percent(int $value, int $total): int
    {
        if ($total <= 0) {
            return 0;
        }

        return (int) round($value / $total * 100);
    }

    /**
     * @return array<int, array{key: string, label: string, applications: int, approved: int}>
     */
    private function buildMemberChart(): array
    {
        $months = $this->emptyMonthBuckets(11, [
            'applications' => 0,
            'approved'     => 0,
        ]);

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

    /**
     * @return array<int, array{key: string, label: string, opportunities: int}>
     */
    private function buildOpportunityChart(): array
    {
        $months = $this->emptyMonthBuckets(5, [
            'opportunities' => 0,
        ]);

        Opportunity::query()
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
     * @param array<string, int> $values
     * @return array<string, array<string, int|string>>
     */
    private function emptyMonthBuckets(int $monthsBack, array $values): array
    {
        $months = [];

        for ($i = $monthsBack; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $key  = $date->format('Y-m');

            $months[$key] = [
                'key'   => $key,
                'label' => $this->monthLabel((int) $date->format('n')) . ' ' . $date->format('y'),
                ...$values,
            ];
        }

        return $months;
    }

    private function monthLabel(int $month): string
    {
        return [
            1  => 'Янв',
            2  => 'Фев',
            3  => 'Мар',
            4  => 'Апр',
            5  => 'Май',
            6  => 'Июн',
            7  => 'Июл',
            8  => 'Авг',
            9  => 'Сен',
            10 => 'Окт',
            11 => 'Ноя',
            12 => 'Дек',
        ][$month] ?? '';
    }
}
