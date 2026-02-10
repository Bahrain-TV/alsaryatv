<?php

namespace App\Livewire;

use App\Models\Caller;
use Livewire\Component;

class ObsOverlayStats extends Component
{
    public int $totalCallers = 0;

    public int $totalWinners = 0;

    public int $todayCallers = 0;

    public int $totalHits = 0;

    public float $winRatio = 0.0;

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

        $winRatio = $totalCallers > 0 ? round(($totalWinners / $totalCallers) * 100, 1) : 0.0;

        $this->totalCallers = $totalCallers;
        $this->totalWinners = $totalWinners;
        $this->todayCallers = $todayCallers;
        $this->totalHits = $totalHits;
        $this->winRatio = $winRatio;
        $this->lastUpdatedAt = now()->format('H:i:s');
    }

    public function render()
    {
        return view('livewire.obs-overlay-stats');
    }
}
