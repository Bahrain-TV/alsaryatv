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
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Ensure Livewire routes have the web middleware
        $this->app->booted(function (): void {
            if (class_exists('\Livewire\Livewire')) {
                // Check if the route exists and add web middleware if needed
                $routes = Route::getRoutes();
                foreach ($routes as $route) {
                    if ($route->getName() === 'livewire.update') {
                        $middleware = $route->middleware();
                        if (! in_array('web', $middleware)) {
                            $route->middleware(['web']);
                        }
                    }
                }

                // If the route doesn't exist, register it manually
                if (! Route::has('livewire.update')) {
                    Route::post('/livewire/update', '\Livewire\Mechanisms\HandleRequests\HandleRequests@handleUpdate')
                        ->middleware(['web'])
                        ->name('livewire.update');
                }
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register the components with their classes
        Blade::component('guest-layout', GuestLayout::class);
        Blade::component('app-layout', AppLayout::class);

        // Default anonymous component namespace
        Blade::componentNamespace('App\\View\\Components', 'app');

        // Define rate limiter for login
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->input('email');

            return Limit::perMinute(5)->by($email.$request->ip());
        });

        FilamentAsset::register([
            // Debug styles with high priority to fix text visibility issues
            Css::make('debug-styles', asset('css/debug-styles.css'))
                ->loadedOnRequest(),
        ]);
    }
}
