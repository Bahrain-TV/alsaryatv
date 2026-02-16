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
     * Scope a query to only include eligible callers for winner selection.
     */
    public function scopeEligible(Builder $query): Builder
    {
        return $query->where('is_winner', false)
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
     * Select a random winner based on CPR uniqueness
     */
    public static function selectRandomWinnerByCpr(): ?Caller
    {
        $winner = self::eligible()->inRandomOrder()->first();

        if ($winner) {
            $winner->update(['is_winner' => true]);
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
