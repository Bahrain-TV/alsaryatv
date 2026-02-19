<?php

use App\Helpers\AssetHelper;

/**
 * Get asset URL with cache-busting query parameter
 *
 * @param  string  $path  Relative path to asset (e.g., 'images/logo.png')
 * @param  string|null  $version  Optional version override
 */
function asset_bust(string $path, ?string $version = null): string
{
    return AssetHelper::bust($path, $version);
}

/**
 * Get image asset URL with cache-busting
 *
 * @param  string  $image  Image filename (e.g., 'logo.png')
 * @param  string|null  $version  Optional version override
 */
function image_bust(string $image, ?string $version = null): string
{
    return AssetHelper::image($image, $version);
}

/**
 * Get CSS asset URL with cache-busting
 *
 * @param  string  $file  CSS filename
 * @param  string|null  $version  Optional version override
 */
function css_bust(string $file, ?string $version = null): string
{
    return AssetHelper::css($file, $version);
}

/**
 * Get JS asset URL with cache-busting
 *
 * @param  string  $file  JS filename
 * @param  string|null  $version  Optional version override
 */
function js_bust(string $file, ?string $version = null): string
{
    return AssetHelper::js($file, $version);
}

/**
 * Get current application version
 */
function app_version(): string
{
    return AssetHelper::getVersion();
}

/**
 * Get version string safe for use in URLs/cache keys
 */
function app_version_safe(): string
{
    return AssetHelper::getVersionSafe();
}
