<?php

namespace App\Filament\Pages;

use App\Models\Caller;
use Filament\Pages\Page;
use BackedEnum;
use UnitEnum;

class WinnerSelection extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-trophy';

    protected string $view = 'filament.pages.winner-selection';

    protected static ?string $navigationLabel = 'اختيار الفائز';

    protected static ?string $title = 'اختيار الفائز';

    protected static string|UnitEnum|null $navigationGroup = 'إدارة المتصلين';

    protected static ?int $navigationSort = 98;

    /**
     * Get eligible callers for winner selection
     */
    public function getEligibleCallers(): array
    {
        $callers = Caller::getEligibleCallers();

        return $callers
            ->map(fn (Caller $caller) => [
                'id' => $caller->id,
                'name' => $caller->name,
                'phone' => $caller->phone,
                'cpr' => $caller->cpr,
                'hits' => $caller->hits,
            ])
            ->toArray();
    }

    /**
     * Get total eligible callers count
     */
    public function getEligibleCallersCount(): int
    {
        return Caller::getEligibleCallers()->count();
    }

    /**
     * Get current winners count
     */
    public function getWinnersCount(): int
    {
        return Caller::where('is_winner', true)->count();
    }

    /**
     * Get total callers count
     */
    public function getTotalCallersCount(): int
    {
        return Caller::count();
    }

    /**
     * Select random winner and mark as winner
     */
    public function selectWinner(): void
    {
        $winner = Caller::selectRandomWinnerByCpr();

        if (!$winner) {
            session()->flash('error', 'لا يوجد متصلين مؤهلين للفوز.');
            return;
        }

        session()->flash('success', 'تم اختيار الفائز: ' . $winner->name . ' (CPR: ' . $winner->cpr . ')');
    }
}
