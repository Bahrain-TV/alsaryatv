<?php

namespace App\Filament\Widgets;

use App\Helpers\PerformanceHelper;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;

class AnimatedStatsOverviewWidget extends Widget
{
    protected string $view = 'filament.widgets.animated-stats-overview';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 1;

    protected static string $cacheKey = 'dashboard_animated_stats';

    public function mount(): void
    {
        $cacheKey = PerformanceHelper::getCacheKey(self::$cacheKey, []);
        $cachedData = Cache::get($cacheKey);

        if ($cachedData) {
            foreach ($cachedData as $property => $value) {
                $this->{$property} = $value;
            }
        } else {
            $this->loadData();
            Cache::put($cacheKey, [
                'totalCallers' => $this->totalCallers,
                'totalWinners' => $this->totalWinners,
                'todayCallers' => $this->todayCallers,
                'totalHits' => $this->totalHits,
                'activeCallers' => $this->activeCallers,
                'uniqueCprs' => $this->uniqueCprs,
                'previousDayCallers' => $this->previousDayCallers,
            ], PerformanceHelper::getCacheTtl('stats'));
        }
    }

    private function loadData(): void
    {
        $this->totalCallers = \App\Models\Caller::count();
        $this->totalWinners = \App\Models\Caller::where('is_winner', true)->count();
        $this->todayCallers = \App\Models\Caller::whereDate('created_at', today())->count();
        $this->totalHits = \App\Models\Caller::sum('hits');
        $this->activeCallers = \App\Models\Caller::where('status', 'accepted')->count();
        $this->uniqueCprs = \App\Models\Caller::distinct('cpr')->count('cpr');
        
        // Calculate previous day callers for trend calculation
        $yesterday = today()->subDay();
        $this->previousDayCallers = \App\Models\Caller::whereDate('created_at', $yesterday)->count();
    }
}