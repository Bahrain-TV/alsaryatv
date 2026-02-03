<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SecurityService
{
    public function validateOperation($user, $operation, $resource = null)
    {
        $cacheKey = "operation_limit:{$user->id}:{$operation}";

        // Use Redis for atomic operations
        return Cache::store('redis')->remember($cacheKey, now()->addMinutes(config('security.rate_limiting.decay_minutes')), function () use ($user, $operation, $resource, $cacheKey) {
            // Get current window data
            $window = Cache::store('redis')->get("window:{$cacheKey}") ?? [
                'count' => 0,
                'start_time' => now()->timestamp,
            ];

            // Check if window should be reset
            if (now()->timestamp - $window['start_time'] >= config('security.rate_limiting.decay_minutes') * 60) {
                $window = [
                    'count' => 0,
                    'start_time' => now()->timestamp,
                ];
            }

            // Check rate limit
            if ($window['count'] >= config('security.rate_limiting.max_attempts')) {
                Log::channel('security')->warning('operation.rate_limit_exceeded', [
                    'user_id' => $user->id,
                    'operation' => $operation,
                    'resource' => $resource,
                    'attempts' => $window['count'],
                    'window_start' => date('Y-m-d H:i:s', $window['start_time']),
                ]);

                return false;
            }

            // Increment counter atomically
            $window['count']++;
            Cache::store('redis')->put("window:{$cacheKey}", $window, now()->addMinutes(config('security.rate_limiting.decay_minutes')));

            return true;
        });
    }

    public function validateRequest($request, array $rules)
    {
        foreach ($rules as $rule => $config) {
            $method = 'validate'.Str::studly($rule);
            if (method_exists($this, $method) && ! $this->$method($request, $config)) {
                return false;
            }
        }

        return true;
    }

    protected function validateHeaders($request, $requiredHeaders)
    {
        foreach ($requiredHeaders as $header) {
            if (! $request->headers->has($header)) {
                Log::channel('security')->warning('request.missing_header', [
                    'header' => $header,
                    'ip' => $request->ip(),
                ]);

                return false;
            }
        }

        return true;
    }

    protected function validateThrottling($request, $config)
    {
        $key = sprintf(
            'throttle:%s:%s:%s',
            $request->ip(),
            $request->route()->getName(),
            now()->format('Y-m-d-H')
        );

        try {
            return Cache::store('redis')->remember("throttle_lock:{$key}", now()->addMinute(), function () use ($request, $config, $key) {
                $window = Cache::store('redis')->get($key, [
                    'requests' => [],
                    'blocked_until' => null,
                ]);

                $currentTime = now()->timestamp;

                // Check if IP is blocked
                if (! empty($window['blocked_until']) && $currentTime < $window['blocked_until']) {
                    Log::channel('security')->warning('request.blocked_ip', [
                        'ip' => $request->ip(),
                        'route' => $request->route()->getName(),
                        'blocked_until' => date('Y-m-d H:i:s', $window['blocked_until']),
                    ]);

                    return false;
                }

                // Reset block if expired
                if (! empty($window['blocked_until']) && $currentTime >= $window['blocked_until']) {
                    $window['blocked_until'] = null;
                    $window['requests'] = [];
                }

                // Clean old requests
                $window['requests'] = array_values(array_filter(
                    $window['requests'],
                    fn ($ts) => $currentTime - $ts < 3600
                ));

                // Check rate limit
                if (count($window['requests']) >= $config['max_attempts']) {
                    $window['blocked_until'] = $currentTime + ($config['blacklist_duration'] * 3600);
                    Cache::store('redis')->put($key, $window, now()->addHours($config['blacklist_duration']));

                    Log::channel('security')->warning('request.throttled', [
                        'ip' => $request->ip(),
                        'route' => $request->route()->getName(),
                        'attempts' => count($window['requests']),
                        'blocked_until' => date('Y-m-d H:i:s', $window['blocked_until']),
                    ]);

                    return false;
                }

                // Add current request
                $window['requests'][] = $currentTime;
                Cache::store('redis')->put($key, $window, now()->addHour());

                return true;
            });
        } catch (\Exception $e) {
            Log::channel('security')->error('throttling.error', [
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);

            return false;
        }
    }
}
