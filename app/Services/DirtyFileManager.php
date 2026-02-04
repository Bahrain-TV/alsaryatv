<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class DirtyFileManager
{
    /**
     * Create a dirty file flag in cache to mark successful registration
     * This indicates the user is viewing the success screen (not rate-limited)
     *
     * @param  int  $ttl  TTL in seconds (default: 60 for the 30-second countdown + buffer)
     */
    public static function markSuccessful(string $cpr, int $ttl = 60): void
    {
        $key = "caller:dirty:{$cpr}";
        Cache::put($key, [
            'timestamp' => now(),
            'session_id' => Session::getId(),
            'marked_at' => microtime(true),
        ], $ttl);
    }

    /**
     * Check if dirty file exists (meaning registration was successful)
     * Returns true if we should show the success screen
     * Returns false if we should show the countdown timer
     */
    public static function exists(string $cpr): bool
    {
        $key = "caller:dirty:{$cpr}";

        return Cache::has($key);
    }

    /**
     * Get dirty file data if it exists
     */
    public static function get(string $cpr): ?array
    {
        $key = "caller:dirty:{$cpr}";

        return Cache::get($key);
    }

    /**
     * Remove dirty file (cleanup after success screen shown)
     */
    public static function remove(string $cpr): void
    {
        $key = "caller:dirty:{$cpr}";
        Cache::forget($key);
    }

    /**
     * Get remaining TTL for dirty file
     *
     * @return int Seconds remaining, or 0 if not found
     */
    public static function getTimeRemaining(string $cpr): int
    {
        // Since Laravel's Cache doesn't directly expose TTL, we calculate from stored timestamp
        $data = self::get($cpr);
        if (! $data) {
            return 0;
        }

        $elapsed = microtime(true) - $data['marked_at'];
        $remaining = max(0, 60 - (int) $elapsed);

        return $remaining;
    }

    /**
     * Check if user is rate-limited (no dirty file exists)
     * This means they're in the countdown state
     *
     * @return bool True if rate-limited (no dirty file)
     */
    public static function isRateLimited(string $cpr): bool
    {
        return ! self::exists($cpr);
    }
}
