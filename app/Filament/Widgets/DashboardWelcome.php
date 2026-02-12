<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class DashboardWelcome extends Widget
{
    protected static string $view = 'filament.widgets.dashboard-welcome';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -1;

    public function getUserName(): string
    {
        return auth()->user()->name ?? 'مدير النظام';
    }

    public function getGreeting(): string
    {
        $hour = now()->hour;
        if ($hour < 12) {
            return 'صباح الخير';
        } elseif ($hour < 18) {
            return 'مساء الخير';
        } else {
            return 'تمنياتنا بمساء سعيد';
        }
    }
}
