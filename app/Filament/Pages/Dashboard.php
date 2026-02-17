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
            // ==================== QUICK ACTIONS (full width, compact on mobile) ====================
            QuickActionsWidget::class,

            // ==================== OVERVIEW STATS (full width) ====================
            AnimatedStatsOverviewWidget::class,

            // ==================== TRENDS (full width chart) ====================
            RegistrationTrendsChart::class,

            // ==================== SIDE-BY-SIDE CHARTS ====================
            PeakHoursChart::class,
            StatusDistributionChart::class,

            // ==================== PARTICIPATION METRICS (full width) ====================
            ParticipationRateWidget::class,

            // ==================== TABLES (side by side on desktop, stacked on mobile) ====================
            RecentActivityWidget::class,
            WinnersHistoryWidget::class,
        ];
    }

    public function getColumns(): array|int
    {
        return [
            'default' => 1, // Mobile: single column (stacked)
            'sm' => 1,      // Small: still single column
            'md' => 2,      // Tablet: 2 columns
            'lg' => 2,      // Desktop: 2 columns for a cleaner layout
            'xl' => 2,      // Large desktop: 2 columns
        ];
    }
}
