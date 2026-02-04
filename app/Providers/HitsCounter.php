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
     * Increment the simulated visit counter
     */
    public static function incrementHits(): int
    {
        // Use a persistent cache key with no expiration (or long TTL)
        return Cache::increment('stats:total_visits', random_int(1, 4));
    }

    /**
     * Get the simulated visit counter
     */
    public static function getHits(): int
    {
        return Cache::rememberForever('stats:total_visits', fn () => random_int(100, 500));
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
        if (!$cpr) return 0;
        return (int) Caller::where('cpr', $cpr)->value('hits');
    }
}
