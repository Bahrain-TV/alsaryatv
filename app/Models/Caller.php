<?php

namespace App\Models;

use App\Services\NtfyNotifier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Caller extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'cpr',
        'is_family',
        'is_winner',
        'is_selected',
        'status',
        'ip_address',
        'hits',
        'last_hit',
        'notes',
        'level',
    ];

    protected $casts = [
        'is_family' => 'boolean',
        'is_winner' => 'boolean',
        'is_selected' => 'boolean',
        'last_hit' => 'datetime',
    ];

    /**
     * Scope a query to only include winners.
     */
    public function scopeWinners(Builder $query): Builder
    {
        return $query->where('is_winner', true);
    }

    /**
     * Scope a query to only include selected callers (from random draw).
     */
    public function scopeSelected(Builder $query): Builder
    {
        return $query->where('is_selected', true);
    }

    /**
     * Scope a query to only include eligible callers for random selection.
     * Eligible = active, has CPR, NOT already selected, NOT already a winner.
     */
    public function scopeEligible(Builder $query): Builder
    {
        return $query->where('is_winner', false)
            ->where('is_selected', false)
            ->where('status', 'active')
            ->whereNotNull('cpr')
            ->where('cpr', '!=', '');
    }

    /**
     * Get eligible callers for winner selection
     */
    public static function getEligibleCallers(): Builder
    {
        return self::eligible();
    }

    /**
     * Select a random winner based on CPR uniqueness.
     * Sets is_selected=true (not is_winner — that's manual).
     */
    public static function selectRandomWinnerByCpr(): ?Caller
    {
        $winner = self::eligible()->inRandomOrder()->first();

        if ($winner) {
            $winner->update([
                'is_selected' => true,
                'status' => 'selected',
            ]);
            app(NtfyNotifier::class)->notifyWinner($winner);
        }

        return $winner;
    }

    /**
     * Increment hits for this caller (atomic operation)
     */
    public function incrementHits(): void
    {
        // Use atomic increment to prevent race conditions
        $this->increment('hits', 1, ['last_hit' => now()]);
        // Refresh instance to reflect database state
        $this->refresh();
    }

    /**
     * Boot method to add event listeners
     */
    protected static function boot()
    {
        parent::boot();

        static::updating(function ($caller) {
            // Allow hits update for everyone
            if ($caller->isDirty('hits') && count($caller->getDirty()) === 1) {
                return true;
            }

            // Allow all updates for authenticated admins
            if (Auth::check() && Auth::user()->is_admin) {
                return true;
            }

            // In production, restrict other updates
            if (app()->environment('production')) {
                return false;
            }

            return true;
        });
    }
}
