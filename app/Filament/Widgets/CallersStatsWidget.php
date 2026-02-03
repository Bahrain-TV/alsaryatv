<?php

namespace App\Filament\Widgets;

use App\Models\Caller;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Number;

class CallersStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s'; // Reduced polling frequency for better performance

    protected function getStats(): array
    {
        // Cache all stats in one go for efficiency
        $stats = $this->getCachedStats();

        return [
            Stat::make('Total Callers', Number::format((int) $stats['total']))
                ->description('All registered callers')
                ->descriptionIcon('heroicon-m-phone')
                ->color('primary')
                ->chart($stats['chart_data']['total'] ?? []),

            Stat::make('Winners', Number::format((int) $stats['winners']))
                ->description('Total contest winners')
                ->descriptionIcon('heroicon-m-trophy')
                ->color('success')
                ->chart($stats['chart_data']['winners'] ?? []),

            Stat::make('Family Members', Number::format((int) $stats['family']))
                ->description('Family members registered')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('warning'),

            Stat::make('Today\'s Callers', Number::format((int) $stats['today']))
                ->description('New callers today')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),
        ];
    }

    protected function getCachedStats(): array
    {
        return Cache::remember('stats.callers.all', now()->addMinutes(5), function () {
            // Get all stats in a single cache entry to reduce database queries
            return [
                'total' => Caller::count(),
                'winners' => Caller::where('is_winner', true)->count(),
                'family' => Caller::where('is_family', true)->count(),
                'today' => Caller::whereDate('created_at', today())->count(),
                'chart_data' => [
                    'total' => $this->getRecentTrend('all'),
                    'winners' => $this->getRecentTrend('winners'),
                ],
            ];
        });
    }

    private function getRecentTrend(string $type): array
    {
        // Generate simple trend data for charts
        $query = Caller::query();

        if ($type === 'winners') {
            $query->where('is_winner', true);
        }

        return $query->where('created_at', '>=', now()->subDays(7))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count')
            ->toArray();
    }
}
