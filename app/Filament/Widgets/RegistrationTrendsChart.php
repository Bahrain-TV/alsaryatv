<?php

namespace App\Filament\Widgets;

use App\Models\Caller;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class RegistrationTrendsChart extends ChartWidget
{
    protected static ?string $heading = 'اتجاهات التسجيل (30 يوم)';

    protected static ?int $sort = 2;

    protected static string $color = 'info';

    protected int | string | array $columnSpan = 'full';

    protected static ?string $maxHeight = '300px';

    protected static ?string $pollingInterval = '60s';

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
            $labels[] = now()->subDays($i)->format('M d');

            $record = $data->firstWhere('date', $date);
            $counts[] = $record ? $record->count : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'التسجيلات اليومية',
                    'data' => $counts,
                    'fill' => true,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'tension' => 0.4,
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
