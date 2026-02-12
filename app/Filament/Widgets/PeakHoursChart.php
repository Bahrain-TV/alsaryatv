<?php

namespace App\Filament\Widgets;

use App\Models\Caller;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;

class PeakHoursChart extends ChartWidget
{
    protected ?string $heading = 'ساعات الذروة';

    protected static ?int $sort = 3;

    protected string $color = 'warning';

    protected int|string|array $columnSpan = [
        'md' => 3,
        'lg' => 6,
    ];

    protected ?string $maxHeight = '300px';

    protected ?string $pollingInterval = '300s';

    protected function getData(): array
    {
        return Cache::remember('dashboard_peak_hours', 300, function () {
            // Get registration counts by hour
            $data = Caller::selectRaw("CAST(strftime('%H', created_at) AS INTEGER) as hour, COUNT(*) as count")
                ->groupBy('hour')
                ->orderBy('hour')
                ->get();

            $labels = [];
            $counts = [];

            for ($i = 0; $i < 24; $i++) {
                $labels[] = sprintf('%02d:00', $i);
                $record = $data->firstWhere('hour', $i);
                $counts[] = $record ? $record->count : 0;
            }

            return [
                'datasets' => [
                    [
                        'label' => 'التسجيلات',
                        'data' => $counts,
                        'backgroundColor' => '#f59e0b',
                        'borderRadius' => 4,
                    ],
                ],
                'labels' => $labels,
            ];
        });
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}
