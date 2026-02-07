<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class QuickActionsWidget extends Widget
{
    protected string $view = 'filament.widgets.quick-actions';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 0;

    public function getQuickActions(): array
    {
        return [
            [
                'title' => 'اختيار فائز يدوي',
                'description' => 'استخدم الدوران لاختيار فائز من المتصلين',
                'icon' => '🏆',
                'url' => '/admin/winner-selection',
                'color' => 'warning',
            ],
            [
                'title' => 'إضافة متصل جديد',
                'description' => 'سجل متصل جديد في النظام',
                'icon' => '➕',
                'url' => '/admin/callers/create',
                'color' => 'info',
            ],
            [
                'title' => 'قائمة الفائزين',
                'description' => 'اعرض جميع الفائزين المسجلين',
                'icon' => '👑',
                'url' => '/admin/callers/winners',
                'color' => 'success',
            ],
            [
                'title' => 'التحليلات المتقدمة',
                'description' => 'عرض التحليلات الشاملة والإحصائيات',
                'icon' => '📊',
                'url' => '/admin/analytics',
                'color' => 'primary',
            ],
        ];
    }
}
