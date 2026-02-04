<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CallersStatsWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'لوحة التحكم';

    protected static ?string $title = 'لوحة التحكم';

    protected static string $view = 'filament.pages.dashboard';

    public function getWidgets(): array
    {
        return [
            CallersStatsWidget::class,
        ];
    }

    public function getColumns(): int|string|array
    {
        return 3;
    }
}
