<?php

namespace App\Providers;

use App\Models\Caller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class HitsCounter extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind('hits', fn () => self::getHits());
        $this->app->bind('total_hits', fn () => self::getTotalHits());
    }

    /**
     * Increment the visit counter
     */
    public static function incrementHits(): int
    {
        // Invalidate cache and return fresh count
        Cache::forget('stats:total_visits');

        return self::getHits();
    }

    /**
     * Get the total visit counter from database
     */
    public static function getHits(): int
    {
        return Cache::rememberForever('stats:total_visits', fn () => (int) Caller::sum('hits'));
    }

    /**
     * Get the total registration hits from DB
     */
    public static function getTotalHits(): int
    {
        return (int) Caller::sum('hits');
    }

    /**
     * Get hits for a specific caller
     */
    public static function getUserHits(?string $cpr): int
    {
        if (! $cpr) {
            return 0;
        }

        return (int) Caller::where('cpr', $cpr)->value('hits');
    }
}
