<?php

namespace App\Livewire;

use App\Models\Caller;
use Livewire\Component;

class AnimatedStatsWidget extends Component
{
    public $totalCallers = 0;

    public $totalWinners = 0;

    public $todayCallers = 0;

    public $totalHits = 0;

    public $activeCallers = 0;

    public $uniqueCprs = 0;

    public $previousDayCallers = 0;

    public $winRatio = 0;

    public $todayTrend = 0;

    public $averageHits = 0;

    public function mount()
    {
        $this->calculateStats();
    }

    public function render()
    {
        return view('livewire.animated-stats-widget');
    }

    public function calculateStats()
    {
        $totalCallers = Caller::count();
        $totalWinners = Caller::where('is_winner', true)->count();
        $todayCallers = Caller::whereDate('created_at', today())->count();
        $totalHits = Caller::sum('hits');
        $activeCallers = Caller::where('status', 'active')->count();
        $uniqueCprs = Caller::distinct('cpr')->count('cpr');

        // Calculate trends
        $previousDayCallers = Caller::whereDate('created_at', today()->subDay())->count();
        $yesterdayCallers = $previousDayCallers;
        $todayTrend = $yesterdayCallers > 0
            ? round((($todayCallers - $yesterdayCallers) / $yesterdayCallers) * 100, 1)
            : ($todayCallers > 0 ? 100 : 0);

        $winRatio = $totalCallers > 0 ? round(($totalWinners / $totalCallers) * 100, 1) : 0;
        $averageHits = $totalCallers > 0 ? round($totalHits / $totalCallers, 1) : 0;

        // Set values with a slight delay to allow for animation
        $this->totalCallers = $totalCallers;
        $this->totalWinners = $totalWinners;
        $this->todayCallers = $todayCallers;
        $this->totalHits = $totalHits;
        $this->activeCallers = $activeCallers;
        $this->uniqueCprs = $uniqueCprs;
        $this->previousDayCallers = $previousDayCallers;
        $this->winRatio = $winRatio;
        $this->todayTrend = $todayTrend;
        $this->averageHits = $averageHits;
    }
}
