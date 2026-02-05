<?php

namespace App\Filament\Widgets;

use App\Models\Caller;
use Filament\Widgets\ChartWidget;

class StatusDistributionChart extends ChartWidget
{
    protected static ?string $heading = 'توزيع حالات المتصلين';

    protected static ?int $sort = 7;

    protected static string $color = 'success';

    protected int | string | array $columnSpan = [
        'sm' => 1,
        'md' => 1,
        'lg' => 2,
    ];

    protected static ?string $maxHeight = '300px';

    protected static ?string $pollingInterval = '120s';

    protected function getData(): array
    {
        $active = Caller::where('status', 'active')->count();
        $inactive = Caller::where('status', 'inactive')->count();
        $blocked = Caller::where('status', 'blocked')->count();

        return [
            'datasets' => [
                [
                    'label' => 'المتصلون',
                    'data' => [$active, $inactive, $blocked],
                    'backgroundColor' => [
                        'rgba(34, 197, 94, 0.8)',  // green for active
                        'rgba(251, 191, 36, 0.8)',  // yellow for inactive
                        'rgba(239, 68, 68, 0.8)',   // red for blocked
                    ],
                    'borderColor' => [
                        'rgb(34, 197, 94)',
                        'rgb(251, 191, 36)',
                        'rgb(239, 68, 68)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => ['نشط', 'غير نشط', 'محظور'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'maintainAspectRatio' => true,
        ];
    }
}
