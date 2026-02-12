<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class QuickActionsWidget extends Widget
{
    protected static string $view = 'filament.widgets.quick-actions';

    protected int|string|array $columnSpan = [
        'md' => 2,
        'lg' => 4,
    ];

    protected static ?int $sort = 0;

    public function getQuickActions(): array
    {
        return [
            [
                'title' => 'اختيار فائز',
                'url' => '/admin/winner-selection',
                'icon' => 'heroicon-m-sparkles',
                'color' => 'warning',
            ],
            [
                'title' => 'إضافة متصل',
                'url' => '/admin/callers/create',
                'icon' => 'heroicon-m-plus-circle',
                'color' => 'info',
            ],
            [
                'title' => 'قائمة الفائزين',
                'url' => '/admin/callers/winners',
                'icon' => 'heroicon-m-trophy',
                'color' => 'success',
            ],
            [
                'title' => 'التحليلات',
                'url' => '/admin/analytics',
                'icon' => 'heroicon-m-chart-bar',
                'color' => 'primary',
            ],
        ];
    }
}
