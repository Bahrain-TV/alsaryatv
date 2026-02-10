<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\App;
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
        // Set Arabic locale for Filament admin panel
        App::setLocale('ar');

        // Register custom Filament pages
        Filament::registerPages([
            \App\Filament\Pages\WinnerSelection::class,
        ]);

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

            // Add RTL and accessibility improvements
            $html = '
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    // Set RTL layout for Arabic
                    document.documentElement.setAttribute("dir", "rtl");
                    document.documentElement.setAttribute("lang", "ar");

                    // Add skip link for screen readers
                    const skipLink = document.createElement("a");
                    skipLink.href = "#main";
                    skipLink.text = "Skip to main content";
                    skipLink.style.position = "absolute";
                    skipLink.style.left = "-10000px";
                    skipLink.style.top = "auto";
                    skipLink.style.width = "1px";
                    skipLink.style.height = "1px";
                    skipLink.style.overflow = "hidden";
                    skipLink.style.backgroundColor = "#f59e0b";
                    skipLink.style.color = "white";
                    skipLink.style.padding = "8px";
                    skipLink.style.borderRadius = "4px";
                    skipLink.style.zIndex = "10000";
                    
                    skipLink.addEventListener("focus", function() {
                        skipLink.style.left = "10px";
                        skipLink.style.top = "10px";
                        skipLink.style.width = "auto";
                        skipLink.style.height = "auto";
                        skipLink.style.overflow = "visible";
                    });
                    
                    skipLink.addEventListener("blur", function() {
                        skipLink.style.left = "-10000px";
                        skipLink.style.top = "auto";
                        skipLink.style.width = "1px";
                        skipLink.style.height = "1px";
                        skipLink.style.overflow = "hidden";
                    });
                    
                    document.body.insertBefore(skipLink, document.body.firstChild);
                    
                    // Improve focus indicators
                    const style = document.createElement("style");
                    style.innerHTML = `
                        :focus-visible {
                            outline: 2px solid #f59e0b !important;
                            outline-offset: 2px !important;
                        }
                        
                        .fi-focus-outline:focus {
                            outline: 2px solid #f59e0b !important;
                            outline-offset: 2px !important;
                        }
                    `;
                    document.head.appendChild(style);
                });
            </script>';

            // Add accessibility script to page
            echo $html;
        });
    }
}
