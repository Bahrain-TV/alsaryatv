<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;

/**
 * Asset Helper - Provides cache-busting functionality for assets
 *
 * Usage in Blade templates:
 *   <img src="{{ asset_bust('images/logo.png') }}" />
 *   <link rel="stylesheet" href="{{ asset_bust('css/app.css') }}">
 */
class AssetHelper
{
    /**
     * Get asset URL with cache-busting query parameter
     *
     * @param  string  $path  Relative path to asset (e.g., 'images/logo.png')
     * @param  string|null  $version  Optional version override (defaults to VERSION file)
     */
    public static function bust(string $path, ?string $version = null): string
    {
        // Get version from VERSION file if not provided
        if ($version === null) {
            $versionFile = base_path('VERSION');
            if (File::exists($versionFile)) {
                $version = trim(File::get($versionFile));
            } else {
                // Fallback to timestamp
                $version = date('YmdHis');
            }
        }

        // Clean version string for use in query parameter
        $version = str_replace(['-', '.'], '_', $version);

        // Get file modification time for additional cache busting
        $fullPath = public_path($path);
        if (File::exists($fullPath)) {
            $mtime = File::lastModified($fullPath);
            $version .= '_'.$mtime;
        }

        return asset($path).'?v='.$version;
    }

    /**
     * Get image asset URL with cache-busting
     *
     * @param  string  $image  Image filename (e.g., 'logo.png')
     * @param  string|null  $version  Optional version override
     */
    public static function image(string $image, ?string $version = null): string
    {
        return self::bust('images/'.$image, $version);
    }

    /**
     * Get CSS asset URL with cache-busting
     *
     * @param  string  $file  CSS filename
     * @param  string|null  $version  Optional version override
     */
    public static function css(string $file, ?string $version = null): string
    {
        return self::bust('css/'.$file, $version);
    }

    /**
     * Get JS asset URL with cache-busting
     *
     * @param  string  $file  JS filename
     * @param  string|null  $version  Optional version override
     */
    public static function js(string $file, ?string $version = null): string
    {
        return self::bust('js/'.$file, $version);
    }

    /**
     * Get current version string
     */
    public static function getVersion(): string
    {
        $versionFile = base_path('VERSION');
        if (File::exists($versionFile)) {
            return trim(File::get($versionFile));
        }

        return config('app.version', 'unknown');
    }

    /**
     * Get version as cache-safe string
     */
    public static function getVersionSafe(): string
    {
        return str_replace(['-', '.'], '_', self::getVersion());
    }
}
