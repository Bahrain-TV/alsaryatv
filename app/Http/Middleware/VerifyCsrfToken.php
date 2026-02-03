<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];

    /**
     * Log CSRF token mismatches for debugging
     */
    protected function logFailure($request)
    {
        \Illuminate\Support\Facades\Log::warning('CSRF token verification failed', [
            'path' => $request->path(),
            'method' => $request->method(),
            'user_ip' => $request->ip(),
            'token_in_request' => $request->input('_token') ? '***' : 'MISSING',
            'token_in_header' => $request->header('X-CSRF-TOKEN') ? '***' : 'MISSING',
            'session_id' => session()->getId(),
            'referer' => $request->header('referer'),
        ]);

        parent::logFailure($request);
    }
}
