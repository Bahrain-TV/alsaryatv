<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;
use SplTempFileObject;

class DumpCallersCsvCommand extends Command
{
    protected $signature = 'app:callers:dump-csv 
                           {--path= : Optional custom path to save the file}
                           {--format=csv : Output format (csv or json)}
                           {--compress : Compress the output file with gzip}';

    protected $aliases = [
        'callers:export' => 'callers:dump-csv',
    ];

    protected $description = 'Generate a file archive of all callers data';

    public function handle()
    {
        try {
            // Fetch callers data from the database
            $callers = DB::table('callers')->get();

            if ($callers->isEmpty()) {
                $this->warn('⚠️ No callers found in the database.');

                return 0;
            }

            // Count callers for reporting
            $callersCount = $callers->count();
            $this->line("Found {$callersCount} callers in the database.");

            // Format timestamp for filename
            $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
            $format = strtolower($this->option('format'));
            $shouldCompress = $this->option('compress');
            $extension = $format === 'json' ? 'json' : 'csv';

            // Generate appropriate content based on format
            $content = '';

            if ($format === 'json') {
                $content = json_encode($callers, JSON_PRETTY_PRINT);
            } else {
                // Create a CSV writer
                $csv = Writer::createFromFileObject(new SplTempFileObject);

                // Insert the headers
                $csv->insertOne([
                    'id',
                    'name',
                    'cpr',
                    'phone',
                    'hits',
                    'last_hit',
                    'status',
                    'notes',
                    'is_family',
                    'is_winner',
                    'created_at',
                    'updated_at',
                ]);

                // Insert the data
                foreach ($callers as $caller) {
                    $csv->insertOne([
                        $caller->id,
                        $caller->name,
                        $caller->cpr,
                        $caller->phone,
                        $caller->hits,
                        // $caller->last_hit,
                        $caller->status,
                        $caller->notes,
                        $caller->is_family,
                        $caller->is_winner,
                        $caller->created_at,
                        $caller->updated_at,
                    ]);
                }

                $content = $csv->toString();
            }

            // Apply compression if requested
            if ($shouldCompress) {
                $content = gzencode($content, 9);
                $extension .= '.gz';
            }

            // Determine where to save the file
            $customPath = $this->option('path');

            if ($customPath) {
                // Ensure the custom path exists
                $directory = dirname($customPath);
                if (! file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }

                // Save directly to the custom path
                file_put_contents($customPath, $content);
                $fullPath = $customPath;
                $this->info("✅ File saved to custom path: {$fullPath}");
            } else {
                // Create directory for exports if it doesn't exist
                $exportDir = "exports/{$format}/callers";
                Storage::makeDirectory($exportDir);

                // Set filename with timestamp and caller count
                $filename = "callers_export_{$callersCount}_records_{$timestamp}.{$extension}";
                $filePath = "{$exportDir}/{$filename}";

                // Save the file to storage
                Storage::put($filePath, $content);
                $fullPath = Storage::path($filePath);
                $this->info("✅ File saved to: {$fullPath}");

                // Add file size information
                $fileSize = Storage::size($filePath);
                $readableSize = $this->formatBytes($fileSize);
                $this->line("📊 File size: {$readableSize}");
            }

            // Log the successful export
            Log::info("Callers export generated with {$callersCount} records in {$format} format".($shouldCompress ? ' (compressed)' : ''));

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Failed to generate export: {$e->getMessage()}");
            Log::error("Error in DumpCallersCsvCommand: {$e->getMessage()}");

            return 1;
        }
    }

    /**
     * Format bytes to human-readable format
     */
    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision).' '.$units[$pow];
    }
}
