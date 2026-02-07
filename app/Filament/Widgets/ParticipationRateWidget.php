<?php

namespace App\Filament\Widgets;

use App\Models\Caller;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ParticipationRateWidget extends BaseWidget
{
    protected ?string $pollingInterval = '60s';

    protected static ?int $sort = 6;

    protected ?string $heading = '🎯 مؤشرات المشاركة والنمو';

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $totalCallers = Caller::count();
        $totalHits = Caller::sum('hits') ?? 0;
        $avgHitsPerCaller = $totalCallers > 0 ? round($totalHits / $totalCallers, 2) : 0;

        $multipleParticipants = Caller::where('hits', '>', 1)->count();
        $participationRate = $totalCallers > 0 ? round(($multipleParticipants / $totalCallers) * 100, 1) : 0;

        $topParticipant = Caller::orderBy('hits', 'desc')->first();
        $thisWeek = Caller::where('created_at', '>=', now()->startOfWeek())->count();
        $lastWeek = Caller::whereBetween('created_at', [
            now()->subWeek()->startOfWeek(),
            now()->subWeek()->endOfWeek(),
        ])->count();

        $weeklyGrowth = $lastWeek > 0
            ? round((($thisWeek - $lastWeek) / $lastWeek) * 100, 1)
            : ($thisWeek > 0 ? 100 : 0);

        return [
            Stat::make('معدل المشاركة لكل متصل', number_format($avgHitsPerCaller, 2))
                ->description("📊 متوسط المشاركات من {$totalHits} المشاركة الإجمالية")
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info')
                ->chart($this->getHitsDistributionChart()),

            Stat::make('المشاركون المتكررون', "{$participationRate}%")
                ->description("👥 {$multipleParticipants} من أصل {$totalCallers} متصل")
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color($participationRate > 50 ? 'success' : 'warning'),

            Stat::make('الأكثر مشاركة', $topParticipant ? $topParticipant->name : '—')
                ->description($topParticipant ? "⭐ {$topParticipant->hits} مشاركة" : 'لا يوجد بيانات')
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),

            Stat::make('النمو الأسبوعي', number_format($thisWeek))
                ->description($this->getGrowthDescription($weeklyGrowth, $lastWeek, $thisWeek))
                ->descriptionIcon($weeklyGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($weeklyGrowth >= 0 ? 'success' : 'danger'),
        ];
    }

    private function getGrowthDescription(float $growth, int $lastWeek, int $thisWeek): string
    {
        if ($growth > 0) {
            return "📈 نمو {$growth}% ({$lastWeek}→{$thisWeek})";
        } elseif ($growth < 0) {
            return '📉 تراجع '.abs($growth)."% ({$lastWeek}→{$thisWeek})";
        }

        return "➡️ لا تغيير ({$lastWeek} = {$thisWeek})";
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
