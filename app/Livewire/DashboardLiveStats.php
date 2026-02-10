<?php

namespace App\Livewire;

use App\Models\Caller;
use Livewire\Component;

class DashboardLiveStats extends Component
{
    public int $totalCallers = 0;

    public int $totalWinners = 0;

    public int $todayCallers = 0;

    public int $totalHits = 0;

    public int $activeCallers = 0;

    public int $uniqueCprs = 0;

    public float $winRatio = 0.0;

    public float $todayTrend = 0.0;

    public float $averageHits = 0.0;

    public ?string $lastWinnerName = null;

    public ?string $lastWinnerPhone = null;

    public ?string $lastCallerName = null;

    public ?string $lastCallerAt = null;

    public string $lastUpdatedAt = '';

    public function mount(): void
    {
        $this->refreshStats();
    }

    public function refreshStats(): void
    {
        $totalCallers = Caller::count();
        $totalWinners = Caller::where('is_winner', true)->count();
        $todayCallers = Caller::whereDate('created_at', today())->count();
        $totalHits = Caller::sum('hits');
        $activeCallers = Caller::where('status', 'active')->count();
        $uniqueCprs = Caller::distinct('cpr')->count('cpr');

        $previousDayCallers = Caller::whereDate('created_at', today()->subDay())->count();
        $todayTrend = $previousDayCallers > 0
            ? round((($todayCallers - $previousDayCallers) / $previousDayCallers) * 100, 1)
            : ($todayCallers > 0 ? 100.0 : 0.0);

        $winRatio = $totalCallers > 0 ? round(($totalWinners / $totalCallers) * 100, 1) : 0.0;
        $averageHits = $totalCallers > 0 ? round($totalHits / $totalCallers, 1) : 0.0;

        $latestWinner = Caller::where('is_winner', true)->latest('updated_at')->first();
        $latestCaller = Caller::latest()->first();

        $this->totalCallers = $totalCallers;
        $this->totalWinners = $totalWinners;
        $this->todayCallers = $todayCallers;
        $this->totalHits = $totalHits;
        $this->activeCallers = $activeCallers;
        $this->uniqueCprs = $uniqueCprs;
        $this->todayTrend = $todayTrend;
        $this->winRatio = $winRatio;
        $this->averageHits = $averageHits;
        $this->lastWinnerName = $latestWinner?->name;
        $this->lastWinnerPhone = $latestWinner?->phone;
        $this->lastCallerName = $latestCaller?->name;
        $this->lastCallerAt = $latestCaller?->created_at?->format('Y-m-d H:i');
        $this->lastUpdatedAt = now()->format('Y-m-d H:i:s');
    }

    public function render()
    {
        return view('livewire.dashboard-live-stats');
    }
}
