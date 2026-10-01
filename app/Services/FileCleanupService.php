<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class FileCleanupService
{
    /**
     * Delete files in a Storage directory that are older than the given number
     * of days, keeping at least $minKeep files regardless of age.
     *
     * @return int Number of files deleted
     */
    public static function cleanupByAge(
        string $directory,
        int $retentionDays = 7,
        int $minKeep = 5,
        ?string $disk = null,
    ): int {
        $storage = $disk ? Storage::disk($disk) : Storage::disk('local');

        if (! $storage->exists($directory)) {
            return 0;
        }

        $files = $storage->files($directory);

        if (count($files) <= $minKeep) {
            return 0;
        }

        // Sort oldest first
        usort($files, fn ($a, $b) => $storage->lastModified($a) - $storage->lastModified($b));

        $cutoff = Carbon::now()->subDays($retentionDays);
        $deleted = 0;

        foreach ($files as $file) {
            if (count($files) - $deleted <= $minKeep) {
                break;
            }

            $lastModified = Carbon::createFromTimestamp($storage->lastModified($file));

            if ($lastModified->lt($cutoff)) {
                $storage->delete($file);
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * Delete old files in a local filesystem directory (non-Storage).
     *
     * @return int Number of files deleted
     */
    public static function cleanupLocalDir(string $dir, int $retentionDays = 7): int
    {
        if (! is_dir($dir)) {
            return 0;
        }

        $cutoff = now()->subDays($retentionDays)->timestamp;
        $deleted = 0;

        foreach (scandir($dir) as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filepath = "{$dir}/{$file}";

            if (is_file($filepath) && filemtime($filepath) < $cutoff) {
                unlink($filepath);
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * Keep only the latest $keep files in a Storage directory, deleting the rest.
     *
     * @return int Number of files deleted
     */
    public static function keepLatest(string $directory, int $keep = 30, ?string $filePattern = null): int
    {
        $files = Storage::files($directory);

        if ($filePattern) {
            $files = array_filter($files, fn ($f) => str_contains($f, $filePattern));
        }

        if (count($files) <= $keep) {
            return 0;
        }

        $filesWithTime = [];
        foreach ($files as $file) {
            $filesWithTime[$file] = Storage::lastModified($file);
        }

        // Sort oldest first
        asort($filesWithTime);

        $toDelete = array_slice($filesWithTime, 0, count($filesWithTime) - $keep);
        $deleted = 0;

        foreach (array_keys($toDelete) as $file) {
            Storage::delete($file);
            $deleted++;
        }

        return $deleted;
    }
}
