<?php

namespace App\Filament\Widgets;

use App\Models\Caller;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class ParticipationRateWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';

    protected static ?int $sort = 6;

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $totalCallers = Caller::count();
        $totalHits = Caller::sum('hits');
        $avgHitsPerCaller = $totalCallers > 0 ? round($totalHits / $totalCallers, 2) : 0;

        $multipleParticipants = Caller::where('hits', '>', 1)->count();
        $participationRate = $totalCallers > 0 ? round(($multipleParticipants / $totalCallers) * 100, 1) : 0;

        $topParticipant = Caller::orderBy('hits', 'desc')->first();
        $this_week = Caller::where('created_at', '>=', now()->startOfWeek())->count();
        $last_week = Caller::whereBetween('created_at', [
            now()->subWeek()->startOfWeek(),
            now()->subWeek()->endOfWeek()
        ])->count();

        $weeklyGrowth = $last_week > 0
            ? round((($this_week - $last_week) / $last_week) * 100, 1)
            : ($this_week > 0 ? 100 : 0);

        return [
            Stat::make('معدل المشاركة لكل متصل', number_format($avgHitsPerCaller, 2))
                ->description('متوسط عدد المشاركات')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info')
                ->chart($this->getHitsDistributionChart()),

            Stat::make('نسبة المشاركين المتكررين', $participationRate . '%')
                ->description("من إجمالي {$totalCallers} متصل")
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color($participationRate > 50 ? 'success' : 'warning'),

            Stat::make('الأكثر مشاركة', $topParticipant ? $topParticipant->name : '-')
                ->description($topParticipant ? "بـ {$topParticipant->hits} مشاركة" : 'لا يوجد')
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),

            Stat::make('النمو الأسبوعي', number_format($this_week))
                ->description($this->getGrowthDescription($weeklyGrowth))
                ->descriptionIcon($weeklyGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($weeklyGrowth >= 0 ? 'success' : 'danger'),
        ];
    }

    private function getGrowthDescription(float $growth): string
    {
        if ($growth > 0) {
            return "نمو {$growth}% عن الأسبوع الماضي";
        } elseif ($growth < 0) {
            return "تراجع " . abs($growth) . "% عن الأسبوع الماضي";
        }
        return 'لا تغيير عن الأسبوع الماضي';
    }

    private function getHitsDistributionChart(): array
    {
        // Get hits distribution for chart
        return Caller::selectRaw('hits')
            ->orderBy('hits', 'desc')
            ->limit(20)
            ->pluck('hits')
            ->toArray();
    }
}
