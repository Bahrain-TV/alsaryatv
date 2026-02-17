<?php

namespace App\Filament\Pages;

use App\Models\Caller;
use Filament\Pages\Page;
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
     * Get eligible callers for winner selection (not selected, not winner)
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
     * Get total eligible callers count (excludes selected & winners)
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
     * Get current selected (but not yet confirmed as winners) count
     */
    public function getSelectedCount(): int
    {
        return Caller::where('is_selected', true)->where('is_winner', false)->count();
    }

    /**
     * Get total callers count
     */
    public function getTotalCallersCount(): int
    {
        return Caller::count();
    }

    /**
     * Select random caller and mark as selected (NOT winner — that's manual).
     */
    public function selectWinner(): void
    {
        $selected = Caller::selectRandomWinnerByCpr();

        if (! $selected) {
            session()->flash('error', 'لا يوجد متصلين مؤهلين للاختيار.');

            return;
        }

        session()->flash('success', 'تم اختيار: '.$selected->name.' (CPR: '.$selected->cpr.') — يمكنك تأكيده كفائز من صفحة المتصلين.');
    }
}
