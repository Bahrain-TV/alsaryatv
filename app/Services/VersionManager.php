<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class VersionManager
{
    private const VERSION_FILE = 'version.json';
    private const SCHEMA_FILE = '.version-schema.json';

    /**
     * Get the current version
     */
    public static function getVersion(): string
    {
        return self::getVersionData()['version'];
    }

    /**
     * Get full version info including metadata
     */
    public static function getVersionInfo(): array
    {
        return self::getVersionData();
    }

    /**
     * Increment patch version (x.x.Z)
     */
    public static function incrementPatch(): string
    {
        return self::incrementVersion('patch');
    }

    /**
     * Increment minor version (x.Y.0)
     */
    public static function incrementMinor(): string
    {
        return self::incrementVersion('minor');
    }

    /**
     * Increment major version (X.0.0)
     */
    public static function incrementMajor(): string
    {
        return self::incrementVersion('major');
    }

    /**
     * Set a specific version
     */
    public static function setVersion(string $version): string
    {
        if (! self::isValidVersion($version)) {
            throw new \InvalidArgumentException("Invalid version format: {$version}. Expected: x.y.z");
        }

        $data = self::getVersionData();
        $data['version'] = $version;
        $data['updated_at'] = now()->toIso8601String();
        $data['updated_by'] = auth()->user()?->email ?? 'system';

        self::writeVersionData($data);

        \Illuminate\Support\Facades\Log::info('Version updated', [
            'version' => $version,
            'updated_by' => $data['updated_by'],
        ]);

        return $version;
    }

    /**
     * Get the branch name (development vs production)
     */
    public static function getBranch(): string
    {
        $branch = trim(shell_exec('git rev-parse --abbrev-ref HEAD 2>/dev/null') ?? 'unknown');
        return $branch ?: 'unknown';
    }

    /**
     * Get the git commit hash
     */
    public static function getCommitHash(): string
    {
        $hash = trim(shell_exec('git rev-parse --short HEAD 2>/dev/null') ?? 'unknown');
        return $hash ?: 'unknown';
    }

    /**
     * Compare versions (returns true if version1 > version2)
     */
    public static function compareVersions(string $version1, string $version2): int
    {
        return version_compare($version1, $version2);
    }

    /**
     * Check if a remote version is newer than current
     */
    public static function isOutdated(string $remoteVersion): bool
    {
        return version_compare($remoteVersion, self::getVersion(), '>');
    }

    /**
     * Get version change log entry
     */
    public static function getChangeLog(): array
    {
        $data = self::getVersionData();
        return $data['changelog'] ?? [];
    }

    /**
     * Add changelog entry
     */
    public static function addChangeLog(string $type, string $message): void
    {
        $data = self::getVersionData();

        if (! isset($data['changelog'])) {
            $data['changelog'] = [];
        }

        array_unshift($data['changelog'], [
            'version' => $data['version'],
            'type' => $type, // 'feature', 'fix', 'improvement', 'security'
            'message' => $message,
            'timestamp' => now()->toIso8601String(),
        ]);

        // Keep only last 100 entries
        $data['changelog'] = array_slice($data['changelog'], 0, 100);

        self::writeVersionData($data);
    }

    /**
     * Private: Increment specific version part
     */
    private static function incrementVersion(string $part): string
    {
        $data = self::getVersionData();
        $version = $data['version'];

        $parts = explode('.', $version);
        if (count($parts) !== 3) {
            throw new \InvalidArgumentException("Invalid version format: {$version}");
        }

        match ($part) {
            'major' => [
                $parts[0]++,
                $parts[1] = 0,
                $parts[2] = 0,
            ],
            'minor' => [
                $parts[1]++,
                $parts[2] = 0,
            ],
            'patch' => $parts[2]++,
            default => throw new \InvalidArgumentException("Invalid part: {$part}"),
        };

        $newVersion = implode('.', $parts);
        $data['version'] = $newVersion;
        $data['updated_at'] = now()->toIso8601String();
        $data['updated_by'] = auth()->user()?->email ?? 'system';

        self::writeVersionData($data);

        \Illuminate\Support\Facades\Log::info("Version incremented ({$part})", [
            'from' => $version,
            'to' => $newVersion,
        ]);

        return $newVersion;
    }

    /**
     * Private: Get version data from file
     */
    private static function getVersionData(): array
    {
        $path = base_path(self::VERSION_FILE);

        if (! File::exists($path)) {
            self::initializeVersion();
        }

        $content = File::get($path);
        return json_decode($content, true) ?? [];
    }

    /**
     * Private: Write version data to file
     */
    private static function writeVersionData(array $data): void
    {
        $path = base_path(self::VERSION_FILE);
        File::put($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Private: Initialize version file if it doesn't exist
     */
    private static function initializeVersion(): void
    {
        $initialData = [
            'version' => '1.0.0',
            'name' => 'AlSarya TV Show Registration System',
            'description' => 'Caller registration platform for TV show',
            'created_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
            'updated_by' => 'system',
            'changelog' => [
                [
                    'version' => '1.0.0',
                    'type' => 'initial',
                    'message' => 'Initial version',
                    'timestamp' => now()->toIso8601String(),
                ],
            ],
        ];

        $path = base_path(self::VERSION_FILE);
        File::put($path, json_encode($initialData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Private: Validate version format
     */
    private static function isValidVersion(string $version): bool
    {
        return preg_match('/^\d+\.\d+\.\d+$/', $version) === 1;
    }
}
