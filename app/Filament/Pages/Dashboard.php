<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AnimatedStatsOverviewWidget;
use App\Filament\Widgets\CallersStatsWidget;
use App\Filament\Widgets\ParticipationRateWidget;
use App\Filament\Widgets\PeakHoursChart;
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
            // ==================== OVERVIEW SECTION ====================
            // Main Stats - First Row
            AnimatedStatsOverviewWidget::class,

            // ==================== PARTICIPATION SECTION ====================
            // Participation Metrics - Second Row
            ParticipationRateWidget::class,

            // ==================== TRENDS & ANALYTICS SECTION ====================
            // Charts Row - Third Row
            RegistrationTrendsChart::class,

            // Charts - Fourth Row (Side by Side)
            PeakHoursChart::class,
            StatusDistributionChart::class,

            // ==================== ACTIVITY & HISTORY SECTION ====================
            // Activity Tables - Fifth Row (Side by Side)
            RecentActivityWidget::class,

            // Winners History - Sixth Row (Full Width)
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