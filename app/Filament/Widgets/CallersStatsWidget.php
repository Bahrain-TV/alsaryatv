<?php

namespace App\Filament\Widgets;

use App\Models\Caller;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CallersStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null; // Disable polling for simplicity

    protected function getStats(): array
    {
        return [
            Stat::make('Total Callers', Caller::count())
                ->description('All registered callers')
                ->descriptionIcon('heroicon-m-phone')
                ->color('primary'),

            Stat::make('Winners', Caller::where('is_winner', true)->count())
                ->description('Total contest winners')
                ->descriptionIcon('heroicon-m-trophy')
                ->color('success'),

            Stat::make('Today\'s Callers', Caller::whereDate('created_at', today())->count())
                ->description('New callers today')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),
        ];
    }
}
