<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class SecurityMonitorService
{
    protected $logChannel = 'security';

    public function logSecurityEvent($event, array $context = [])
    {
        $context = array_merge([
            'timestamp' => now()->toIso8601String(),
            'request_id' => request()->header('X-Request-ID'),
            'ip' => request()->ip(),
            'user_id' => auth()->id() ?? 'guest',
            'user_agent' => request()->userAgent(),
        ], $context);

        Log::channel($this->logChannel)->info($event, $context);
    }

    public function logSecurityViolation($violation, array $context = [])
    {
        $context = array_merge([
            'timestamp' => now()->toIso8601String(),
            'request_id' => request()->header('X-Request-ID'),
            'ip' => request()->ip(),
            'user_id' => auth()->id() ?? 'guest',
            'user_agent' => request()->userAgent(),
            'path' => request()->path(),
            'method' => request()->method(),
        ], $context);

        Log::channel($this->logChannel)->warning($violation, $context);

        $this->checkForAttackPattern($context);
    }

    protected function checkForAttackPattern($context)
    {
        $key = 'security_violations:'.$context['ip'];
        $violations = Cache::get($key, []);

        $violations[] = [
            'timestamp' => now()->timestamp,
            'type' => $context['violation'] ?? 'unknown',
        ];

        // Keep only violations from last hour
        $violations = array_filter($violations, fn ($v) => $v['timestamp'] > now()->subHour()->timestamp
        );

        if (count($violations) >= config('security.max_violations_per_hour', 10)) {
            $this->blockIP($context['ip']);
            Event::dispatch('security.ip_blocked', [
                'ip' => $context['ip'],
                'reason' => 'Excessive security violations',
                'violations' => $violations,
            ]);
        }

        Cache::put($key, $violations, now()->addHour());
    }

    public function blockIP($ip, $duration = 24)
    {
        $key = 'blocked_ip:'.$ip;
        Cache::put($key, true, now()->addHours($duration));

        Log::channel($this->logChannel)->alert('IP blocked', [
            'ip' => $ip,
            'duration' => $duration,
            'until' => now()->addHours($duration)->toIso8601String(),
        ]);
    }

    public function isIPBlocked($ip): bool
    {
        return Cache::has('blocked_ip:'.$ip);
    }
}
