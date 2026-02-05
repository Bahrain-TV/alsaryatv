<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CallersStatsWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets;

class Dashboard extends BaseDashboard
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'لوحة التحكم';

    protected static ?string $title = 'لوحة التحكم';

    public function getWidgets(): array
    {
        return [
            CallersStatsWidget::class,
        ];
    }

    public function getColumns(): array|int
    {
        return 3;
    }
}