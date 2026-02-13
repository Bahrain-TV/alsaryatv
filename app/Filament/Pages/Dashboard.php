<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AnimatedStatsOverviewWidget;
use App\Filament\Widgets\ParticipationRateWidget;
use App\Filament\Widgets\PeakHoursChart;
use App\Filament\Widgets\QuickActionsWidget;
use App\Filament\Widgets\RecentActivityWidget;
use App\Filament\Widgets\RegistrationTrendsChart;
use App\Filament\Widgets\StatusDistributionChart;
use App\Filament\Widgets\WinnersHistoryWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'لوحة التحكم';

    protected static ?string $title = 'لوحة التحكم';

    public function getWidgets(): array
    {
        return [
            // ==================== QUICK ACTIONS ====================
            QuickActionsWidget::class,

            // ==================== OVERVIEW SECTION ====================
            AnimatedStatsOverviewWidget::class,

            // ==================== TRENDS & ANALYTICS ====================
            RegistrationTrendsChart::class,

            // ==================== CHARTS (Side by Side) ====================
            PeakHoursChart::class,
            StatusDistributionChart::class,

            // ==================== PARTICIPATION METRICS ====================
            ParticipationRateWidget::class,

            // ==================== ACTIVITY & WINNERS ====================
            RecentActivityWidget::class,
            WinnersHistoryWidget::class,
        ];
    }

    public function getColumns(): array|int
    {
        return [
            'sm' => 1,   // Mobile: 1 column
            'md' => 2,   // Tablet: 2 columns
            'lg' => 4,   // Desktop: 4 columns
        ];
    }
}
