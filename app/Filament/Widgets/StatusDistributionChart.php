<?php

namespace App\Filament\Widgets;

use App\Models\Caller;
use Filament\Widgets\ChartWidget;

class StatusDistributionChart extends ChartWidget
{
    protected ?string $heading = 'توزيع الحالات';

    protected static ?int $sort = 7;

    protected string $color = 'success';

    protected int|string|array $columnSpan = [
        'md' => 3,
        'lg' => 6,
    ];

    protected ?string $maxHeight = '300px';

    protected ?string $pollingInterval = '120s';

    protected function getData(): array
    {
        $active = Caller::where('status', 'active')->count();
        $inactive = Caller::where('status', 'inactive')->count();
        $blocked = Caller::where('status', 'blocked')->count();

        return [
            'datasets' => [
                [
                    'label' => 'المتصلين',
                    'data' => [$active, $inactive, $blocked],
                    'backgroundColor' => [
                        '#10b981', // emerald-500
                        '#f59e0b', // amber-500
                        '#ef4444', // red-500
                    ],
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
        ];
    }
}
