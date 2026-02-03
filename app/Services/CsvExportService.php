<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;

class CsvExportService
{
    protected $filename;

    protected $directory;

    protected $headers;

    protected $retentionDays = 7;

    public function __construct(string $filename = 'callers-export.csv')
    {
        $this->filename = $filename;
        $this->directory = 'private/exports/callers/csv';
        $this->headers = ['name', 'phone', 'cpr', 'is_family', 'is_winner', 'status', 'notes', 'hits', 'last_hit', 'created_at'];

        // Clean up old files
        $this->cleanupOldExports();
    }

    /**
     * Generate CSV file from callers data
     *
     * @param  \Illuminate\Support\Collection  $callers
     * @return string Path to generated CSV file
     *
     * @throws \Exception
     */
    public function generate($callers)
    {
        // Validate input
        if (! $callers instanceof Collection && ! is_array($callers)) {
            throw new \InvalidArgumentException('Callers must be a Collection or array');
        }

        if ($callers instanceof Collection && $callers->isEmpty() ||
            is_array($callers) && empty($callers)) {
            throw new \InvalidArgumentException('Callers collection cannot be empty');
        }

        try {
            // Create directory if it doesn't exist
            if (! Storage::exists($this->directory)) {
                Storage::makeDirectory($this->directory);
                Log::info('Created export directory: '.$this->directory);
            }

            // Create CSV writer
            $csv = Writer::createFromString('');

            // Insert headers
            $csv->insertOne($this->headers);

            // Process records in batches
            $records = [];
            $recordCount = 0;

            foreach ($callers as $caller) {
                $records[] = [
                    $caller->name ?? '',
                    $caller->phone ?? '',
                    $caller->cpr ?? '',
                    $caller->is_family ? 'true' : 'false',
                    $caller->is_winner ? 'true' : 'false',
                    $caller->status ?? 'active',
                    $caller->notes ?? '',
                    $caller->hits ?? 0,
                    $caller->last_hit ? $caller->last_hit->format('Y-m-d H:i:s') : '',
                    $caller->created_at ? $caller->created_at->format('Y-m-d H:i:s') : '',
                ];

                $recordCount++;

                // Process in batches of 1000
                if (count($records) >= 1000) {
                    $csv->insertAll($records);
                    $records = [];
                }
            }

            // Insert any remaining records
            if (! empty($records)) {
                $csv->insertAll($records);
            }

            // Generate timestamped filename
            $timestamp = now()->format('Ymd_His');
            $filenameWithTimestamp = pathinfo($this->filename, PATHINFO_FILENAME).
                                      '_'.$timestamp.'.'.
                                      pathinfo($this->filename, PATHINFO_EXTENSION);

            // Save to storage
            $path = $this->directory.'/'.$filenameWithTimestamp;
            Storage::put($path, $csv->toString());

            Log::info('CSV export completed', [
                'path' => $path,
                'record_count' => $recordCount,
            ]);

            return $path;

        } catch (\Exception $e) {
            Log::error('CSV export failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $this->filename,
            ]);

            throw new \Exception('Failed to generate CSV file: '.$e->getMessage());
        }
    }

    /**
     * Clean up old export files while ensuring at least 5 files are kept
     */
    protected function cleanupOldExports(): void
    {
        try {
            if (! Storage::exists($this->directory)) {
                return;
            }

            $files = Storage::files($this->directory);

            // If we have 5 or fewer files, don't delete any
            if (count($files) <= 5) {
                return;
            }

            // Sort files by modified time, oldest first
            usort($files, function ($a, $b) {
                return Storage::lastModified($a) - Storage::lastModified($b);
            });

            $cutoffDate = Carbon::now()->subDays($this->retentionDays);
            $deletedCount = 0;

            // Keep deleting old files as long as we still have more than 5 left
            foreach ($files as $file) {
                $lastModified = Carbon::createFromTimestamp(Storage::lastModified($file));

                if ($lastModified->lt($cutoffDate) && (count($files) - $deletedCount > 5)) {
                    Storage::delete($file);
                    Log::info('Deleted old export file', ['file' => $file]);
                    $deletedCount++;
                }
            }
        } catch (\Exception $e) {
            Log::error('Error cleaning up old exports: '.$e->getMessage());
            // Don't throw the exception as this is a background cleanup task
        }
    }

    /**
     * Set retention period for export files
     *
     * @return $this
     */
    public function setRetentionDays(int $days): self
    {
        $this->retentionDays = $days;

        return $this;
    }

    /**
     * Set custom headers for the CSV file
     *
     * @return $this
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Set custom directory for the CSV file
     *
     * @return $this
     */
    public function setDirectory(string $directory): self
    {
        $this->directory = $directory;

        return $this;
    }

    /**
     * Set custom field mapping for the CSV export
     *
     * @param  Collection|array  $items
     * @return string Path to generated CSV file
     *
     * @throws \Exception
     */
    public function generateWithMapping($items, callable $mappingCallback)
    {
        if (! $items instanceof Collection && ! is_array($items)) {
            throw new \InvalidArgumentException('Items must be a Collection or array');
        }

        if ($items instanceof Collection && $items->isEmpty() ||
            is_array($items) && empty($items)) {
            throw new \InvalidArgumentException('Items collection cannot be empty');
        }

        try {
            // Create directory if it doesn't exist
            if (! Storage::exists($this->directory)) {
                Storage::makeDirectory($this->directory);
                Log::info('Created export directory: '.$this->directory);
            }

            // Create CSV writer
            $csv = Writer::createFromString('');

            // Insert headers
            $csv->insertOne($this->headers);

            // Process records in batches
            $records = [];
            $recordCount = 0;

            foreach ($items as $item) {
                $records[] = $mappingCallback($item);
                $recordCount++;

                // Process in batches of 1000
                if (count($records) >= 1000) {
                    $csv->insertAll($records);
                    $records = [];
                }
            }

            // Insert any remaining records
            if (! empty($records)) {
                $csv->insertAll($records);
            }

            // Generate timestamped filename
            $timestamp = now()->format('Ymd_His');
            $filenameWithTimestamp = pathinfo($this->filename, PATHINFO_FILENAME).
                                      '_'.$timestamp.'.'.
                                      pathinfo($this->filename, PATHINFO_EXTENSION);

            // Save to storage
            $path = $this->directory.'/'.$filenameWithTimestamp;
            Storage::put($path, $csv->toString());

            Log::info('CSV export completed', [
                'path' => $path,
                'record_count' => $recordCount,
            ]);

            return $path;

        } catch (\Exception $e) {
            Log::error('CSV export failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $this->filename,
            ]);

            throw new \Exception('Failed to generate CSV file: '.$e->getMessage());
        }
    }
}
