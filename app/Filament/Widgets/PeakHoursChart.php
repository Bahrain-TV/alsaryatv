<?php

namespace App\Filament\Widgets;

use App\Models\Caller;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PeakHoursChart extends ChartWidget
{
    protected static ?string $heading = 'ساعات الذروة للتسجيل';

    protected static ?int $sort = 3;

    protected static string $color = 'warning';

    protected int | string | array $columnSpan = [
        'sm' => 1,
        'md' => 1,
        'lg' => 2,
    ];

    protected static ?string $maxHeight = '300px';

    protected static ?string $pollingInterval = '120s';

    protected function getData(): array
    {
        // Get registration counts by hour
        $data = Caller::selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $labels = [];
        $counts = [];

        // Fill all 24 hours
        for ($i = 0; $i < 24; $i++) {
            $labels[] = sprintf('%02d:00', $i);
            $record = $data->firstWhere('hour', $i);
            $counts[] = $record ? $record->count : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'عدد التسجيلات',
                    'data' => $counts,
                    'backgroundColor' => [
                        'rgba(251, 191, 36, 0.8)',
                    ],
                    'borderColor' => 'rgb(251, 191, 36)',
                    'borderWidth' => 2,
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
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
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
