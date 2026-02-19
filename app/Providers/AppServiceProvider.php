<?php

namespace App\Providers;

use App\View\Components\AppLayout;
use App\View\Components\GuestLayout;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register standard Blade components
        Blade::component('guest-layout', GuestLayout::class);
        Blade::component('app-layout', AppLayout::class);

        // Define rate limiter for login
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('email').$request->ip());
        });

        // Register Filament assets
        FilamentAsset::register([
            Css::make('debug-styles', asset('css/debug-styles.css'))->loadedOnRequest(),
        ]);

        // Ensure local images are cache-busted after deployments by appending file-modified timestamps.
        // This appends ?v=<filemtime> to local image, favicon and lottie URLs found in HTML responses.
        $router = $this->app->make(\Illuminate\Routing\Router::class);
        $router->pushMiddlewareToGroup('web', \App\Http\Middleware\AppendImageVersion::class);

        // Persistence Hooks: Automatically backup/restore data during migrations
        if ($this->app->runningInConsole()) {
            $this->registerMigrationHooks();
        }
    }

    /**
     * Register migration persistence hooks
     */
    private function registerMigrationHooks(): void
    {
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Console\Events\CommandStarting::class, function ($event): void {
            if (in_array($event->command, ['migrate', 'db:seed', 'migrate:fresh', 'migrate:refresh', 'migrate:rollback'])) {
                \Illuminate\Support\Facades\Artisan::call('app:persist-data', ['--export-csv' => true, '--verify' => true]);
            }
        });

        \Illuminate\Support\Facades\Event::listen(\Illuminate\Console\Events\CommandFinished::class, function ($event): void {
            if ($event->exitCode === 0 && in_array($event->command, ['migrate', 'db:seed', 'migrate:fresh', 'migrate:refresh'])) {
                \Illuminate\Support\Facades\Artisan::call('app:callers:import', ['--force' => true]);
            }
        });
    }
}
