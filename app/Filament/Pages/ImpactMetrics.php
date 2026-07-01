<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\BotUser;
use App\Models\Opportunity;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class ImpactMetrics extends Page
{
    protected string $view = 'filament.pages.impact-metrics';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Статистика';

    protected static ?string $title = 'Impact Metrics';

    protected static ?int $navigationSort = 2;

    /**
     * @return array<string, mixed>
     */
    public function getViewData(): array
    {
        // ── Members ──────────────────────────────────────────────────
        $totalMembers   = BotUser::count();
        $approvedCount  = BotUser::approved()->count();
        $pendingCount   = BotUser::pending()->count();
        $rejectedCount  = BotUser::rejected()->count();
        $withProfile    = BotUser::approved()->whereNotNull('description')->where('description', '!=', '')->count();
        $withEmbedding  = BotUser::approved()->whereNotNull('embedding')->count();

        // ── Opportunities ─────────────────────────────────────────────
        $opportunitiesTotal   = Opportunity::count();
        $oppByType = Opportunity::query()
            ->select('type', DB::raw('count(*) as cnt'))
            ->groupBy('type')
            ->pluck('cnt', 'type')
            ->toArray();

        // ── Registration dynamics — last 12 months ────────────────────
        $registrationsByMonth = BotUser::query()
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('COUNT(*) as cnt')
            )
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('cnt', 'month')
            ->toArray();

        // Fill in any missing months with 0
        $registrationChart = [];
        for ($i = 11; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $registrationChart[$key] = $registrationsByMonth[$key] ?? 0;
        }

        // ── Opportunities by month — last 6 months ────────────────────
        $oppByMonth = Opportunity::query()
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('COUNT(*) as cnt')
            )
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('cnt', 'month')
            ->toArray();

        $oppChart = [];
        for ($i = 5; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $oppChart[$key] = $oppByMonth[$key] ?? 0;
        }

        // ── Latest members (for quick view) ──────────────────────────
        $latestMembers = BotUser::approved()
            ->latest('approved_at')
            ->limit(5)
            ->get(['full_name', 'telegram_username', 'approved_at', 'description']);

        return [
            'totalMembers'       => $totalMembers,
            'approvedCount'      => $approvedCount,
            'pendingCount'       => $pendingCount,
            'rejectedCount'      => $rejectedCount,
            'withProfile'        => $withProfile,
            'withEmbedding'      => $withEmbedding,
            'opportunitiesTotal' => $opportunitiesTotal,
            'oppByType'          => $oppByType,
            'registrationChart'  => $registrationChart,
            'oppChart'           => $oppChart,
            'latestMembers'      => $latestMembers,
            'generatedAt'        => now()->format('d.m.Y H:i'),
        ];
    }
}
