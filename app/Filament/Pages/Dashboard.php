<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CallersStatsWidget;
use App\Filament\Widgets\ParticipationRateWidget;
use App\Filament\Widgets\PeakHoursChart;
use App\Filament\Widgets\RecentActivityWidget;
use App\Filament\Widgets\RegistrationTrendsChart;
use App\Filament\Widgets\StatusDistributionChart;
use App\Filament\Widgets\WinnersHistoryWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets;

class Dashboard extends BaseDashboard
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'لوحة التحكم';

    protected static ?string $title = 'لوحة التحكم - برنامج السارية';

    public function getWidgets(): array
    {
        return [
            // Main Stats - First Row
            CallersStatsWidget::class,

            // Participation Metrics - Second Row
            ParticipationRateWidget::class,

            // Charts Row - Third Row
            RegistrationTrendsChart::class,

            // Charts - Fourth Row (Side by Side)
            PeakHoursChart::class,
            StatusDistributionChart::class,

            // Activity Tables - Fifth Row (Side by Side)
            RecentActivityWidget::class,

            // Winners History - Sixth Row (Full Width)
            WinnersHistoryWidget::class,
        ];
    }

    public function getColumns(): array|int
    {
        return [
            'sm' => 1,
            'md' => 2,
            'lg' => 4,
        ];
    }
}