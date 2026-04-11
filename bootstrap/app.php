<?php

use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SetLocale::class,
        ]);

        // Exempt webhook and static assets from maintenance mode
        $middleware->preventRequestsDuringMaintenance(except: [
            '/api/deploy-webhook',
            '/build/*',
            '/images/*',
            '/lottie/*',
            '/css/*',
            '/js/*',
            '/fonts/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
        $exceptions->render(function (HttpException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getStatusCode());
        });

    })->create();
