<?php

namespace App\Filament\Widgets;

use App\Models\Caller;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class CallersStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s'; // Refresh every 30 seconds

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalCallers = Caller::count();
        $totalWinners = Caller::where('is_winner', true)->count();
        $todayCallers = Caller::whereDate('created_at', today())->count();
        $totalHits = Caller::sum('hits');
        $activeCallers = Caller::where('status', 'active')->count();
        $uniqueCprs = Caller::distinct('cpr')->count('cpr');

        // Calculate trends
        $yesterdayCallers = Caller::whereDate('created_at', today()->subDay())->count();
        $todayTrend = $yesterdayCallers > 0
            ? round((($todayCallers - $yesterdayCallers) / $yesterdayCallers) * 100, 1)
            : ($todayCallers > 0 ? 100 : 0);

        return [
            Stat::make('إجمالي المتصلين', number_format($totalCallers))
                ->description('جميع المتصلين المسجلين')
                ->descriptionIcon('heroicon-m-phone')
                ->color('primary')
                ->chart($this->getRegistrationChart()),

            Stat::make('الفائزون', number_format($totalWinners))
                ->description('إجمالي الفائزين في المسابقة')
                ->descriptionIcon('heroicon-m-trophy')
                ->color('success'),

            Stat::make('متصلو اليوم', number_format($todayCallers))
                ->description($this->getTrendDescription($todayTrend))
                ->descriptionIcon($todayTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($todayTrend >= 0 ? 'info' : 'warning'),

            Stat::make('إجمالي المشاركات', number_format($totalHits))
                ->description('عدد مرات المشاركة الكلي')
                ->descriptionIcon('heroicon-m-hand-raised')
                ->color('warning'),

            Stat::make('المتصلون النشطون', number_format($activeCallers))
                ->description('الحسابات النشطة حالياً')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('الأرقام الفريدة', number_format($uniqueCprs))
                ->description('عدد المتصلين الفريدين (CPR)')
                ->descriptionIcon('heroicon-m-identification')
                ->color('gray'),
        ];
    }

    private function getTrendDescription(float $trend): string
    {
        if ($trend > 0) {
            return "زيادة {$trend}% عن الأمس";
        } elseif ($trend < 0) {
            return "انخفاض " . abs($trend) . "% عن الأمس";
        }
        return 'لا تغيير عن الأمس';
    }

    private function getRegistrationChart(): array
    {
        // Get registration counts for the last 7 days
        $data = Caller::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count')
            ->toArray();

        // Ensure we have 7 data points
        while (count($data) < 7) {
            array_unshift($data, 0);
        }

        return $data;
    }
}
