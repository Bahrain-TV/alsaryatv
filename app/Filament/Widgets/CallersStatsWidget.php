<?php

namespace App\Filament\Widgets;

use App\Models\Caller;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class CallersStatsWidget extends BaseWidget
{
    protected ?string $pollingInterval = '30s';

    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $totalCallers = Caller::count();
        $totalWinners = Caller::where('is_winner', true)->count();
        $todayCallers = Caller::whereDate('created_at', today())->count();
        $totalHits = Caller::sum('hits') ?? 0;
        
        // Calculate trends
        $yesterdayCallers = Caller::whereDate('created_at', today()->subDay())->count();
        $todayTrend = $yesterdayCallers > 0
            ? round((($todayCallers - $yesterdayCallers) / $yesterdayCallers) * 100, 1)
            : ($todayCallers > 0 ? 100 : 0);

        $winRatio = $totalCallers > 0 ? round(($totalWinners / $totalCallers) * 100, 1) : 0;
        $avgHits = $totalCallers > 0 ? round($totalHits / $totalCallers, 1) : 0;

        return [
            Stat::make('المتصلين', number_format($totalCallers))
                ->value(number_format($totalCallers))
                ->description('إجمالي المسجلين')
                ->descriptionIcon('heroicon-m-phone')
                ->chart($this->getStatsChart(null))
                ->color('primary'),

            Stat::make('الفائزين', number_format($totalWinners))
                ->description("{$winRatio}% من المشاركين")
                ->descriptionIcon('heroicon-m-trophy')
                ->chart($this->getStatsChart(true))
                ->color('success'),

            Stat::make('متصلو اليوم', number_format($todayCallers))
                ->description($this->getTrendDescription($todayTrend))
                ->descriptionIcon($todayTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($this->getTodayChart())
                ->color($todayTrend >= 0 ? 'info' : 'warning'),

            Stat::make('المشاركات', number_format($totalHits))
                ->description("متوسط {$avgHits} لكل متصل")
                ->descriptionIcon('heroicon-m-hand-raised')
                ->chart($this->getHitsChart())
                ->color('warning'),
        ];
    }

    private function getTrendDescription(float $trend): string
    {
        if ($trend > 0) return "نمو بنسبة {$trend}% عن أمس";
        if ($trend < 0) return "انخفاض بنسبة " . abs($trend) . "% عن أمس";
        return 'ثبات المعدل اليومي';
    }

    private function getStatsChart(?bool $winnersOnly): array
    {
        $query = Caller::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(10))
            ->groupBy('date')
            ->orderBy('date');

        if ($winnersOnly !== null) {
            $query->where('is_winner', $winnersOnly);
        }

        $data = $query->pluck('count')->toArray();
        
        return count($data) > 0 ? $data : [0, 0, 0, 0, 0, 0, 0];
    }

    private function getTodayChart(): array
    {
        // Hourly data for today
        $data = Caller::selectRaw("CAST(strftime('%H', created_at) AS INTEGER) as hour, COUNT(*) as count")
            ->whereDate('created_at', today())
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('count')
            ->toArray();

        return count($data) > 0 ? $data : [0, 1, 0, 2, 1];
    }

    private function getHitsChart(): array
    {
        // Top 10 participation hit counts
        return Caller::orderBy('hits', 'desc')
            ->limit(10)
            ->pluck('hits')
            ->toArray();
    }
}
