<?php

namespace App\Console\Commands;

use App\Models\Caller;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class DumpCallersCommand extends Command
{
    protected $signature = 'app:callers:dump 
                        {--no-compress : Disable compression (ZIP) of exported files}
                        {--format=csv : Output format (csv, json)}
                        {--batch-size=1000 : Records per batch}
                        {--with-status=* : Filter by specific status}
                        {--folder= : Custom subfolder for output files}';

    protected $description = 'Export callers data to CSV/JSON with compression by default';

    public function handle()
    {
        $this->info('Starting callers data export...');

        $batchSize = (int) $this->option('batch-size');
        $format = strtolower($this->option('format'));
        $compress = ! $this->option('no-compress'); // Default is compression enabled
        $statuses = $this->option('with-status');
        $customFolder = $this->option('folder');

        // Validate inputs
        if (! in_array($format, ['csv', 'json'])) {
            $this->error("Invalid format: {$format}. Supported formats: csv, json");

            return 1;
        }

        if ($batchSize < 1) {
            $this->warn('Invalid batch size. Using default: 1000');
            $batchSize = 1000;
        }

        try {
            $this->info('Export configuration:');
            $this->line("- Format: {$format}");
            $this->line('- Compression: '.($compress ? 'Enabled' : 'Disabled'));
            $this->line("- Batch size: {$batchSize}");

            if (! empty($statuses)) {
                $this->line('- Status filter: '.implode(', ', $statuses));
            }

            // Create base directory with optional custom folder
            $basePath = "private/exports/{$format}/callers";
            if ($customFolder) {
                $basePath .= "/{$customFolder}";
            }

            Storage::makeDirectory($basePath);

            // Build query with optional status filter
            $query = Caller::query();
            if (! empty($statuses)) {
                $query->whereIn('status', $statuses);
            }

            // Get total count for progress bar
            $total = $query->count();
            if ($total === 0) {
                $this->warn('No records found matching criteria.');

                return 1;
            }

            $this->info("Found {$total} records. Processing in batches of {$batchSize}...");

            // Show progress
            $bar = $this->output->createProgressBar($total);
            $bar->start();

            $fileCounter = 0;
            $processedRecords = 0;
            $generatedFiles = [];
            $timestamp = Carbon::now()->format('Ymd_His');

            // Process in batches
            $query->chunk($batchSize, function ($callers) use (
                &$processedRecords, &$fileCounter, $format, $basePath, $bar, &$generatedFiles, $timestamp
            ): void {
                $filename = "callers_export_{$timestamp}_{$fileCounter}.{$format}";
                $fullPath = "{$basePath}/{$filename}";

                try {
                    if ($format === 'csv') {
                        $this->exportToCSV($callers, $fullPath);
                    } else {
                        $this->exportToJSON($callers, $fullPath);
                    }

                    $generatedFiles[] = $fullPath;
                    $fileCounter++;
                    $processedRecords += $callers->count();
                    $bar->advance($callers->count());
                } catch (\Exception $e) {
                    Log::error("Error exporting batch {$fileCounter}: ".$e->getMessage());
                    // Continue processing other batches
                }
            });

            $bar->finish();
            $this->newLine(2);

            if (empty($generatedFiles)) {
                $this->error('No files were generated!');

                return 1;
            }

            // Compress files if requested
            if ($compress && ! empty($generatedFiles)) {
                $zipFilename = "callers_export_{$timestamp}.zip";
                $zipPath = "{$basePath}/{$zipFilename}";

                try {
                    $compressionStartTime = microtime(true);
                    $this->info("Compressing {$fileCounter} export files...");

                    $success = $this->compressFiles($generatedFiles, $zipPath);

                    if ($success) {
                        $compressionTime = round(microtime(true) - $compressionStartTime, 2);
                        $this->info("Files successfully compressed to: {$zipPath} in {$compressionTime}s");

                        $zipSize = Storage::size($zipPath);
                        $this->line('ZIP file size: '.$this->formatBytes($zipSize));

                        // Delete individual files after successful compression
                        foreach ($generatedFiles as $file) {
                            Storage::delete($file);
                        }

                        $this->info('Removed original uncompressed files.');
                    } else {
                        $this->error('Compression failed!');
                    }
                } catch (\Exception $e) {
                    $this->error('Compression error: '.$e->getMessage());
                }
            }

            $this->info("Export completed successfully. {$processedRecords} records processed into {$fileCounter} files.");

            return 0;

        } catch (\Exception $e) {
            $this->error('Export failed: '.$e->getMessage());
            Log::error('Callers export failed: '.$e->getMessage());

            return 1;
        }
    }

    protected function exportToCSV($callers, $path)
    {
        $handle = fopen(Storage::path($path), 'w');

        if (! $handle) {
            throw new \Exception("Could not open file for writing: {$path}");
        }

        try {
            // Add CSV headers
            fputcsv($handle, ['ID', 'Name', 'Phone', 'CPR', 'Is Family', 'Is Winner', 'Hits', 'Status', 'Created At']);

            // Add data rows
            foreach ($callers as $caller) {
                fputcsv($handle, [
                    $caller->id,
                    $caller->name,
                    $caller->phone_number,
                    $caller->cpr,
                    $caller->is_family ? 'Yes' : 'No',
                    $caller->is_winner ? 'Yes' : 'No',
                    $caller->hits,
                    $caller->status,
                    $caller->created_at,
                ]);
            }
        } finally {
            fclose($handle);
        }
    }

    protected function exportToJSON($callers, $path)
    {
        Storage::put($path, $callers->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    protected function compressFiles($files, $zipPath)
    {
        if (! extension_loaded('zip')) {
            $this->error('ZIP extension is not available!');

            return false;
        }

        $zip = new ZipArchive;
        $fullZipPath = Storage::path($zipPath);

        if ($zip->open($fullZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            try {
                foreach ($files as $file) {
                    $relativeName = basename($file);
                    $fullFilePath = Storage::path($file);

                    if (file_exists($fullFilePath)) {
                        $zip->addFile($fullFilePath, $relativeName);
                    } else {
                        $this->warn("File not found: {$file}");
                    }
                }
            } finally {
                $zip->close();
            }

            return file_exists($fullZipPath);
        }

        return false;
    }

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
