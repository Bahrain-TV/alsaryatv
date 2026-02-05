<?php

namespace App\Filament\Pages;

use App\Models\Caller;
use Filament\Pages\Page;
use Filament\Support\Enums\IconSize;
use Illuminate\Support\Facades\DB;
use BackedEnum;

class Analytics extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static string $view = 'filament.pages.analytics';

    protected static ?string $navigationLabel = 'التحليلات';

    protected static ?string $title = 'تحليلات شاملة';

    protected static \UnitEnum|string|null $navigationGroup = 'إدارة المتصلين';

    protected static ?int $navigationSort = 99;

    /**
     * Get comprehensive analytics data
     */
    public function getAnalyticsData(): array
    {
        // Overview Stats
        $totalCallers = Caller::count();
        $totalWinners = Caller::where('is_winner', true)->count();
        $totalHits = Caller::sum('hits');
        $uniqueCprs = Caller::distinct('cpr')->count('cpr');

        // Status breakdown
        $activeCallers = Caller::where('status', 'active')->count();
        $inactiveCallers = Caller::where('status', 'inactive')->count();
        $blockedCallers = Caller::where('status', 'blocked')->count();

        // Time-based stats
        $todayCallers = Caller::whereDate('created_at', today())->count();
        $thisWeekCallers = Caller::whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ])->count();
        $thisMonthCallers = Caller::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Participation metrics
        $avgHitsPerCaller = $totalCallers > 0 ? round($totalHits / $totalCallers, 2) : 0;
        $highParticipation = Caller::where('hits', '>', 5)->count();
        $lowParticipation = Caller::where('hits', '<=', 2)->count();

        // Top performers
        $topParticipants = Caller::orderBy('hits', 'desc')->limit(10)->get();
        $recentWinners = Caller::where('is_winner', true)
            ->latest('updated_at')
            ->limit(5)
            ->get();

        // Registration trends (last 30 days)
        $registrationTrends = Caller::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        // Peak hours analysis
        $peakHours = Caller::selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        // Growth rate
        $lastWeekCallers = Caller::whereBetween('created_at', [
            now()->subWeek()->startOfWeek(),
            now()->subWeek()->endOfWeek()
        ])->count();

        $weeklyGrowthRate = $lastWeekCallers > 0
            ? round((($thisWeekCallers - $lastWeekCallers) / $lastWeekCallers) * 100, 1)
            : ($thisWeekCallers > 0 ? 100 : 0);

        return [
            'overview' => [
                'total_callers' => $totalCallers,
                'total_winners' => $totalWinners,
                'total_hits' => $totalHits,
                'unique_cprs' => $uniqueCprs,
                'today_callers' => $todayCallers,
                'this_week_callers' => $thisWeekCallers,
                'this_month_callers' => $thisMonthCallers,
            ],
            'status' => [
                'active' => $activeCallers,
                'inactive' => $inactiveCallers,
                'blocked' => $blockedCallers,
            ],
            'participation' => [
                'avg_hits_per_caller' => $avgHitsPerCaller,
                'high_participation' => $highParticipation,
                'low_participation' => $lowParticipation,
            ],
            'top_performers' => $topParticipants,
            'recent_winners' => $recentWinners,
            'registration_trends' => $registrationTrends,
            'peak_hours' => $peakHours,
            'growth' => [
                'weekly_growth_rate' => $weeklyGrowthRate,
                'last_week' => $lastWeekCallers,
                'this_week' => $thisWeekCallers,
            ],
        ];
    }

    /**
     * Get comparison data (this period vs last period)
     */
    public function getComparisonData(): array
    {
        // This week vs last week
        $thisWeek = Caller::whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ])->count();

        $lastWeek = Caller::whereBetween('created_at', [
            now()->subWeek()->startOfWeek(),
            now()->subWeek()->endOfWeek()
        ])->count();

        // This month vs last month
        $thisMonth = Caller::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $lastMonth = Caller::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        return [
            'weekly' => [
                'current' => $thisWeek,
                'previous' => $lastWeek,
                'change' => $lastWeek > 0 ? round((($thisWeek - $lastWeek) / $lastWeek) * 100, 1) : 0,
            ],
            'monthly' => [
                'current' => $thisMonth,
                'previous' => $lastMonth,
                'change' => $lastMonth > 0 ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1) : 0,
            ],
        ];
    }
}
