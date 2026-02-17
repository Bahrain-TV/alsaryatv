<?php

namespace App\Filament\Widgets;

use App\Models\Caller;
use Filament\Widgets\ChartWidget;

class StatusDistributionChart extends ChartWidget
{
    protected ?string $heading = '📈 توزيع حالات المتصلين';

    protected static ?int $sort = 7;

    protected string $color = 'success';

    protected int|string|array $columnSpan = [
        'default' => 1,
        'sm' => 1,
        'md' => 1,
        'lg' => 1,
    ];

    protected ?string $maxHeight = '320px';

    protected ?string $pollingInterval = '120s';

    protected function getData(): array
    {
        $active = Caller::where('status', 'active')->count();
        $inactive = Caller::where('status', 'inactive')->count();
        $selected = Caller::where('status', 'selected')->count();
        $blocked = Caller::where('status', 'blocked')->count();
        $total = $active + $inactive + $selected + $blocked;

        $activePercent = $total > 0 ? round(($active / $total) * 100, 1) : 0;
        $inactivePercent = $total > 0 ? round(($inactive / $total) * 100, 1) : 0;
        $selectedPercent = $total > 0 ? round(($selected / $total) * 100, 1) : 0;
        $blockedPercent = $total > 0 ? round(($blocked / $total) * 100, 1) : 0;

        return [
            'datasets' => [
                [
                    'label' => 'عدد المتصلين',
                    'data' => [$active, $inactive, $selected, $blocked],
                    'backgroundColor' => [
                        'rgba(34, 197, 94, 0.85)',      // green for active
                        'rgba(251, 191, 36, 0.85)',     // yellow for inactive
                        'rgba(59, 130, 246, 0.85)',     // blue for selected
                        'rgba(239, 68, 68, 0.85)',      // red for blocked
                    ],
                    'borderColor' => [
                        'rgb(34, 197, 94)',
                        'rgb(251, 191, 36)',
                        'rgb(59, 130, 246)',
                        'rgb(239, 68, 68)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => [
                "نشط ($active) {$activePercent}%",
                "غير نشط ($inactive) {$inactivePercent}%",
                "مُختار ($selected) {$selectedPercent}%",
                "محظور ($blocked) {$blockedPercent}%",
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => true,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'right',
                    'labels' => [
                        'padding' => 15,
                        'font' => [
                            'size' => 12,
                        ],
                        'generateLabels' => 'function(chart) { return chart.data.labels.map((label, i) => ({text: label, fillStyle: chart.data.datasets[0].backgroundColor[i]})); }',
                    ],
                ],
                'tooltip' => [
                    'backgroundColor' => 'rgba(0, 0, 0, 0.8)',
                    'padding' => 12,
                    'titleFont' => ['size' => 12],
                    'bodyFont' => ['size' => 11],
                ],
            ],
        ];
    }
}
