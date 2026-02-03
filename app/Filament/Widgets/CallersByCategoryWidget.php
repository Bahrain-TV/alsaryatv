<?php

namespace App\Filament\Widgets;

use App\Models\Caller;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;

class CallersByCategoryWidget extends ChartWidget
{
    protected static ?string $heading = 'Callers by Category';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        return Cache::remember('chart.callers.by_category', now()->addMinutes(10), function () {
            $winners = Caller::winners()->count();
            $family = Caller::where('is_family', true)->count();
            $regular = Caller::where('is_winner', false)
                ->where('is_family', false)
                ->count();

            return [
                'datasets' => [
                    [
                        'label' => 'Callers by Category',
                        'data' => [$regular, $family, $winners],
                        'backgroundColor' => ['#4BC0C0', '#FFCD56', '#FF6384'],
                    ],
                ],
                'labels' => ['Regular Callers', 'Family Members', 'Winners'],
            ];
        });
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
