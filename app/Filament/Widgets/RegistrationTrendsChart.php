<?php

namespace App\Filament\Widgets;

use App\Models\Caller;
use Filament\Widgets\ChartWidget;

class RegistrationTrendsChart extends ChartWidget
{
    protected ?string $heading = '📊 اتجاهات التسجيل - آخر 30 يوم';

    protected static ?int $sort = 2;

    protected string $color = 'info';

    protected int|string|array $columnSpan = [
        'default' => 1,
        'sm' => 1,
        'md' => 2,
        'lg' => 2,
    ];

    protected ?string $maxHeight = '350px';

    protected ?string $pollingInterval = '60s';

    protected function getData(): array
    {
        // Get registration data for the last 30 days
        $data = Caller::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Create labels and data arrays
        $labels = [];
        $counts = [];

        // Fill in missing dates with 0
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('d M');

            $record = $data->firstWhere('date', $date);
            $counts[] = $record ? $record->count : 0;
        }

        // Calculate statistics
        $maxCount = max($counts) ?: 1;
        $avgCount = round(array_sum($counts) / count($counts), 1);
        $totalCount = array_sum($counts);

        return [
            'datasets' => [
                [
                    'label' => "التسجيلات اليومية (المتوسط: {$avgCount})",
                    'data' => $counts,
                    'fill' => true,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.15)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                    'tension' => 0.4,
                    'pointRadius' => 4,
                    'pointBackgroundColor' => 'rgb(59, 130, 246)',
                    'pointBorderColor' => 'rgb(255, 255, 255)',
                    'pointBorderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
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
                        'padding' => 15,
                        'font' => [
                            'size' => 12,
                        ],
                    ],
                ],
                'tooltip' => [
                    'backgroundColor' => 'rgba(0, 0, 0, 0.8)',
                    'padding' => 12,
                    'titleFont' => ['size' => 13],
                    'bodyFont' => ['size' => 12],
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
                        'font' => ['size' => 11],
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'عدد التسجيلات',
                    ],
                ],
                'x' => [
                    'ticks' => [
                        'font' => ['size' => 11],
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'التاريخ',
                    ],
                ],
            ],
        ];
    }
}
