<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

trait SecureOperations
{
    /**
     * Check if a rate limit has been exceeded
     *
     * @param  string  $key  The rate limit key
     * @param  int  $maxAttempts  Maximum number of attempts allowed
     * @param  int  $decaySeconds  Decay time in seconds
     * @return bool Returns false if rate limit exceeded, true otherwise
     */
    protected function checkRateLimit(string $key, int $maxAttempts, int $decaySeconds): bool
    {
        // Use the configured cache driver (database by default)
        // No Redis dependency - uses whatever is configured in .env (CACHE_STORE)
        $attempts = Cache::get($key, 0);

        if ($attempts >= $maxAttempts) {
            $this->logSecurityEvent('rate_limit.exceeded', [
                'key' => $key,
                'attempts' => $attempts,
                'max' => $maxAttempts,
                'ip' => request()->ip(),
            ]);

            return false;
        }

        Cache::put($key, $attempts + 1, $decaySeconds);

        return true;
    }

    /**
     * Log a security event
     *
     * @param  string  $event  The event name
     * @param  array  $context  Additional context data
     */
    protected function logSecurityEvent(string $event, array $context = []): void
    {
        // Append standard security information
        $context = array_merge($context, [
            'user_id' => Auth::id() ?? 'guest',
            'session_id' => session()->getId(),
            'request_id' => Str::uuid()->toString(),
            'timestamp' => now()->toIso8601String(),
            'user_agent' => request()->userAgent(),
            'method' => request()->method(),
            'path' => request()->path(),
        ]);

        // Log to a dedicated security channel
        Log::channel('security')->info("Security event: {$event}", $context);

        // Also log to standard log for critical events
        if (Str::contains($event, ['failed', 'exceeded', 'attempt', 'invalid', 'error'])) {
            Log::warning("Security alert: {$event}", $context);
        }
    }
}
