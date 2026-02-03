<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\ServiceProvider;

class FilamentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Filament::serving(function (): void {
            // Register all CSS files using FilamentAsset facade
            FilamentAsset::register([
                // Debug styles with high priority to fix text visibility issues
                Css::make(
                    'debug-styles',
                    'css/debug-styles.css'
                )->loadedOnRequest(),
                // Google Font for Tajawal
                Css::make(
                    'google-fonts',
                    'https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap'
                ),
                // Custom CSS file (previously registered with deprecated method)

            ]);

            // Remove the deprecated registerStyles method
        });
    }
}
