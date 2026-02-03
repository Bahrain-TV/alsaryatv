<?php

namespace App\Providers;

use App\Models\Caller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

class HitsCounter extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind('hits', function () {
            return self::getHits();
        });
        $this->app->bind('total_hits', function () {
            return self::getTotalHits();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }

    public static function incrementHits()
    {
        try {
            $hits = self::getHits();
            // Simple increment based on time of day
            $currentHour = (int) now()->format('H');
            $increment = ($currentHour >= 18 || $currentHour < 6) ? 1 : random_int(1, 4);

            $hits += $increment;
            Storage::put('hits.txt', $hits);

            return $hits;
        } catch (\Exception $e) {
            Log::error('Failed to increment hits: '.$e->getMessage());

            return 0;
        }
    }

    public static function getHits()
    {
        try {
            if (! Storage::exists('hits.txt')) {
                Storage::put('hits.txt', random_int(1, 100));
            }

            return (int) Storage::get('hits.txt');
        } catch (\Exception $e) {
            Log::error('Failed to get hits: '.$e->getMessage());

            return 0;
        }
    }

    public static function getTotalHits()
    {
        try {
            return Caller::sum('hits') ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    public static function getUserHits($cpr)
    {
        try {
            return Caller::where('cpr', $cpr)->value('hits') ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }
}
