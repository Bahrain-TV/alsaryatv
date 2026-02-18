<?php

namespace App\Providers;

use App\Helpers\AssetHelper;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AssetServiceProvider extends ServiceProvider
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
        // Register @assetbust directive
        Blade::directive('assetbust', function ($expression) {
            return "<?php echo \App\Helpers\AssetHelper::bust({$expression}); ?>";
        });

        // Register @imagebust directive for images
        Blade::directive('imagebust', function ($expression) {
            return "<?php echo \App\Helpers\AssetHelper::image({$expression}); ?>";
        });

        // Register @cssbust directive for stylesheets
        Blade::directive('cssbust', function ($expression) {
            return "<?php echo \App\Helpers\AssetHelper::css({$expression}); ?>";
        });

        // Register @jsbust directive for scripts
        Blade::directive('jsbust', function ($expression) {
            return "<?php echo \App\Helpers\AssetHelper::js({$expression}); ?>";
        });

        // Register @version directive
        Blade::directive('version', function () {
            return "<?php echo \App\Helpers\AssetHelper::getVersion(); ?>";
        });

        // Register @versionsafe directive
        Blade::directive('versionsafe', function () {
            return "<?php echo \App\Helpers\AssetHelper::getVersionSafe(); ?>";
        });
    }
}
