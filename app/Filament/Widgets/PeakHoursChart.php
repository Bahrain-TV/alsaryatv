<?php

namespace App\Filament\Widgets;

use App\Models\Caller;
use Filament\Widgets\ChartWidget;

class PeakHoursChart extends ChartWidget
{
    protected ?string $heading = '⏰ ساعات الذروة للتسجيل';

    protected static ?int $sort = 3;

    protected string $color = 'warning';

    protected int|string|array $columnSpan = [
        'sm' => 1,
        'md' => 1,
        'lg' => 2,
    ];

    protected ?string $maxHeight = '320px';

    protected ?string $pollingInterval = '120s';

    protected function getData(): array
    {
        // Get registration counts by hour
        $data = Caller::selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $labels = [];
        $counts = [];
        $peakHour = 0;
        $maxCount = 0;

        // Fill all 24 hours
        for ($i = 0; $i < 24; $i++) {
            $labels[] = sprintf('%02d:00', $i);
            $record = $data->firstWhere('hour', $i);
            $count = $record ? $record->count : 0;
            $counts[] = $count;

            if ($count > $maxCount) {
                $maxCount = $count;
                $peakHour = $i;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => "عدد التسجيلات (الذروة في {$labels[$peakHour]})",
                    'data' => $counts,
                    'backgroundColor' => 'rgba(251, 191, 36, 0.7)',
                    'borderColor' => 'rgb(251, 191, 36)',
                    'borderWidth' => 2,
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => true,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                    'labels' => [
                        'padding' => 10,
                        'font' => ['size' => 11],
                    ],
                ],
                'tooltip' => [
                    'backgroundColor' => 'rgba(0, 0, 0, 0.8)',
                    'padding' => 10,
                    'titleFont' => ['size' => 12],
                    'callbacks' => [
                        'label' => 'function(context) { return "التسجيلات: " + context.parsed.y; }',
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                        'font' => ['size' => 10],
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'العدد',
                        'font' => ['size' => 11],
                    ],
                ],
                'x' => [
                    'ticks' => [
                        'font' => ['size' => 9],
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'الساعة',
                        'font' => ['size' => 11],
                    ],
                ],
            ],
        ];
    }
}
