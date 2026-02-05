<?php

namespace App\Filament\Widgets;

use App\Models\Caller;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CallersStatsWidget extends BaseWidget
{
    protected ?string $pollingInterval = '30s';

    protected static ?int $sort = 1;

    protected ?string $heading = 'ملخص الإحصائيات الرئيسية';

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

        $winRatio = $totalCallers > 0 ? round(($totalWinners / $totalCallers) * 100, 1) : 0;

        return [
            Stat::make('إجمالي المتصلين', number_format($totalCallers))
                ->description("📞 جميع المتصلين المسجلين في النظام")
                ->descriptionIcon('heroicon-m-phone')
                ->color('primary')
                ->chart($this->getRegistrationChart()),

            Stat::make('الفائزون', number_format($totalWinners))
                ->description("🏆 {$winRatio}% من إجمالي المتصلين")
                ->descriptionIcon('heroicon-m-trophy')
                ->color('success'),

            Stat::make('متصلو اليوم', number_format($todayCallers))
                ->description($this->getTrendDescription($todayTrend))
                ->descriptionIcon($todayTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($todayTrend >= 0 ? 'info' : 'warning'),

            Stat::make('إجمالي المشاركات', number_format($totalHits))
                ->description("👋 متوسط {$this->getAverageHits($totalCallers, $totalHits)} مشاركة لكل متصل")
                ->descriptionIcon('heroicon-m-hand-raised')
                ->color('warning'),

            Stat::make('المتصلون النشطون', number_format($activeCallers))
                ->description("✅ " . ($totalCallers > 0 ? round(($activeCallers / $totalCallers) * 100, 1) : 0) . "% من المتصلين")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('أرقام فريدة (CPR)', number_format($uniqueCprs))
                ->description("🔐 عدد المتصلين الفريدين حسب رقم المواطن")
                ->descriptionIcon('heroicon-m-identification')
                ->color('gray'),
        ];
    }

    private function getTrendDescription(float $trend): string
    {
        if ($trend > 0) {
            return "📈 زيادة {$trend}% عن الأمس";
        } elseif ($trend < 0) {
            return "📉 انخفاض " . abs($trend) . "% عن الأمس";
        }
        return '➡️ لا تغيير عن الأمس';
    }

    private function getAverageHits(int $totalCallers, int $totalHits): string
    {
        if ($totalCallers === 0) {
            return '0';
        }
        return number_format(round($totalHits / $totalCallers, 1), 1);
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
