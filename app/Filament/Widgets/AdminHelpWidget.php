<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class AdminHelpWidget extends Widget
{
    protected string $view = 'filament.widgets.admin-help';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 200;

    public function getHelpTopics(): array
    {
        return [
            [
                'title' => 'كيفية اختيار الفائز',
                'description' => 'استخدم صفحة اختيار الفائز للدوران واختيار متصل عشوائي كفائز',
                'icon' => '🎯',
            ],
            [
                'title' => 'تصدير البيانات',
                'description' => 'يمكنك تصدير قائمة المتصلين بصيغة CSV أو Excel من صفحة المتصلين',
                'icon' => '📥',
            ],
            [
                'title' => 'إدارة الحالات',
                'description' => 'قم بتغيير حالة المتصلين (نشط/غير نشط/محظور) من خلال الإجراءات السريعة',
                'icon' => '⚙️',
            ],
            [
                'title' => 'التحليلات والإحصائيات',
                'description' => 'اعرض الإحصائيات المفصلة والتحليلات في صفحة التحليلات المتقدمة',
                'icon' => '📊',
            ],
        ];
    }
}
