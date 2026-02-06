<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class AnimatedStatsOverviewWidget extends Widget
{
    protected string $view = 'filament.widgets.animated-stats-overview';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 1;
}