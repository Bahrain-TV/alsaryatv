<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CallersStatsWidget;
use App\Filament\Widgets\DashboardWelcome;
use App\Filament\Widgets\PeakHoursChart;
use App\Filament\Widgets\QuickActionsWidget;
use App\Filament\Widgets\RecentActivityWidget;
use App\Filament\Widgets\RegistrationTrendsChart;
use App\Filament\Widgets\StatusDistributionChart;
use App\Filament\Widgets\WinnersHistoryWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'الرئيسية';

    protected static ?string $title = 'لوحة التحكم المركزية';

    public function getWidgets(): array
    {
        return [
            // Row 1: Welcome message (Full width)
            DashboardWelcome::class,

            // Row 2: Stats (Responsive)
            CallersStatsWidget::class,

            // Row 3: Main Trend Chart (Wide) and Quick Actions (Narrow)
            RegistrationTrendsChart::class,
            QuickActionsWidget::class,

            // Row 4: Analytics Charts (Side by side)
            PeakHoursChart::class,
            StatusDistributionChart::class,

            // Row 5: Recent Activity (Table)
            RecentActivityWidget::class,

            // Row 6: Winners History (Full width footer)
            WinnersHistoryWidget::class,
        ];
    }

    public function getColumns(): array|int
    {
        return [
            'sm' => 1,
            'md' => 6,
            'lg' => 12,
        ];
    }
}
