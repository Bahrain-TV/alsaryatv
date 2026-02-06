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
                // Google Font for Tajawal
                Css::make(
                    'google-fonts',
                    'https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap'
                ),
                
                // Beautiful custom admin theme
                Css::make(
                    'beautiful-admin-theme',
                    asset('css/filament/admin/theme.css')
                ),

            ], 'aldoyh/alsaryatv');

            // Remove the deprecated registerStyles method
        });
    }
}
