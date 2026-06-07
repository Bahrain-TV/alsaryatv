<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class EnvFileUpdater
{
    /**
     * Update or append a key=value pair in a .env file.
     *
     * @return bool True if the file was modified, false if already up-to-date or file missing
     */
    public static function set(string $envPath, string $key, string $value): bool
    {
        if (! File::exists($envPath)) {
            return false;
        }

        $content = File::get($envPath);
        $pattern = '/^'.preg_quote($key, '/').'=.*/m';

        if (preg_match($pattern, $content)) {
            $newContent = preg_replace($pattern, "{$key}={$value}", $content);
        } else {
            $newContent = rtrim($content)."\n{$key}={$value}\n";
        }

        if ($newContent === $content) {
            return false;
        }

        File::put($envPath, $newContent);

        return true;
    }

    /**
     * Update APP_VERSION across standard .env files.
     *
     * @return int Number of files updated
     */
    public static function syncAppVersion(string $version): int
    {
        $envFiles = [
            base_path('.env'),
            base_path('.env.local'),
            base_path('.env.production'),
        ];

        $updated = 0;
        foreach ($envFiles as $path) {
            if (self::set($path, 'APP_VERSION', $version)) {
                $updated++;
            }
        }

        return $updated;
    }
}
