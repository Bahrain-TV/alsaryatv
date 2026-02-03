<?php

namespace App\Console\Commands;

use App\Models\Caller;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Csv\Reader;

class ImportCallers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:callerss:import {--truncate : Whether to truncate the callers table before import} {--batch-size=500 : Size of batches for processing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import callers from the latest CSV file in storage';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startTime = microtime(true);
        $batchSize = (int) $this->option('batch-size');

        // Check if we should truncate the table
        if ($this->option('truncate')) {
            if ($this->confirm('Are you sure you want to truncate the callers table? This will delete all existing caller records.')) {
                $this->info('Truncating callers table...');
                Caller::truncate();
                $this->info('Callers table truncated.');
            } else {
                $this->info('Truncate operation cancelled.');
            }
        }

        // Find the most recent CSV file in the specific directory
        $csvFile = $this->findLatestCsvFile();
        if (! $csvFile) {
            $this->error('No CSV files found in the expected directory.');

            return 1;
        }

        $this->info("Using CSV file: {$csvFile}");
        $this->line('Last modified: '.date('Y-m-d H:i:s', Storage::lastModified($csvFile)));
        $this->line('File size: '.number_format(Storage::size($csvFile) / 1024, 2).' KB');

        try {
            // Directly stream the file instead of copying to temp file
            $stream = Storage::readStream($csvFile);

            // Parse the CSV file
            $csv = Reader::createFromStream($stream);
            $csv->setHeaderOffset(0); // The first row contains headers

            // Display headers for verification
            $headers = $csv->getHeader();
            $this->info('CSV Headers: '.implode(', ', $headers));

            // Get total records for progress tracking
            $totalRecords = count($csv);
            $this->info("Found {$totalRecords} records to import");

            // Setup progress bar with fewer updates
            $bar = $this->output->createProgressBar($totalRecords);
            $bar->setRedrawFrequency(max(1, intval($totalRecords / 100))); // Update only 100 times
            $bar->start();

            $records = $csv->getRecords();
            $count = 0;
            $inserted = 0;
            $updated = 0;
            $skipped = 0;
            $errors = [];

            // Get existing CPRs for faster lookups
            $this->info('Pre-loading existing CPRs (this will speed up the import)...');
            $existingCprMap = $this->getExistingCprMap();
            $this->info('Loaded '.count($existingCprMap).' existing CPRs');

            // Process in batches for better performance
            $batch = [];
            $currentBatch = 0;

            foreach ($records as $record) {
                try {
                    // Process record into normalized data
                    $processedRecord = $this->processRecord($record, $existingCprMap);

                    if ($processedRecord['action'] === 'skip') {
                        $skipped++;
                    } else {
                        $batch[] = $processedRecord['data'];
                    }

                    // Process batch when it reaches the batch size
                    if (count($batch) >= $batchSize) {
                        $results = $this->processBatch($batch, $existingCprMap);
                        $inserted += $results['inserted'];
                        $updated += $results['updated'];
                        $batch = [];
                        $currentBatch++;

                        // Show progress
                        $bar->advance(count($batch));
                    }

                    $count++;

                } catch (\Exception $e) {
                    $errors[] = [
                        'record' => $record,
                        'error' => $e->getMessage(),
                    ];
                }
            }

            // Process any remaining records
            if (! empty($batch)) {
                $results = $this->processBatch($batch, $existingCprMap);
                $inserted += $results['inserted'];
                $updated += $results['updated'];
            }

            $bar->finish();
            $this->newLine(2);

            // Close the stream
            if (is_resource($stream)) {
                fclose($stream);
            }

            $endTime = microtime(true);
            $executionTime = round($endTime - $startTime, 2);

            $this->info("Import completed in {$executionTime} seconds:");
            $this->info("- Total processed: {$count} records");
            $this->info("- New records inserted: {$inserted}");
            $this->info("- Existing records updated: {$updated}");
            $this->info("- Records skipped: {$skipped}");
            $this->info('- Processing speed: '.round($count / max(1, $executionTime), 2).' records/second');

            if (! empty($errors)) {
                $this->warn('There were '.count($errors)." problematic records that couldn't be imported.");
                // Save error records to a file for review
                $errorPath = storage_path('app/caller_import_errors_'.date('YmdHis').'.json');
                if (! file_exists(dirname($errorPath))) {
                    mkdir(dirname($errorPath), 0755, true);
                }
                file_put_contents($errorPath, json_encode($errors, JSON_PRETTY_PRINT));
                $this->info("Error records saved to: {$errorPath}");
            }

            return 0;

        } catch (\Exception $e) {
            $this->error('Error importing CSV: '.$e->getMessage());
            Log::error('CSV import error: '.$e->getMessage(), [
                'file' => $csvFile,
                'trace' => $e->getTraceAsString(),
            ]);

            return 1;
        }
    }

    /**
     * Find the latest CSV file in the specific directory
     *
     * @return string|null
     */
    protected function findLatestCsvFile()
    {
        // Define the specific directory where the CSV file is located
        $targetDirectory = storage_path('app/private/exports/callers');

        $this->info("Looking for CSV files in: {$targetDirectory}");

        $latestFile = null;
        $latestTime = 0;

        // Check if directory exists
        if (! Storage::exists($targetDirectory)) {
            $this->warn("Directory does not exist: {$targetDirectory}");

            return null;
        }

        // Get all files in the directory
        $files = Storage::glob($targetDirectory.'/*');

        $this->info('Found '.count($files)." files in directory: {$targetDirectory}");

        // Filter for CSV files only
        $csvFiles = array_filter($files, function ($file) {
            return Str::endsWith($file, '.csv');
        });

        if (empty($csvFiles)) {
            $this->warn("No CSV files found in directory: {$targetDirectory}");

            // If no files found in primary directory, try to find the specific file
            // $specificFile = $targetDirectory . '/callers_export_23222_records_2025-03-08_00-15-54.csv';

        }

        $this->info('Found '.count($csvFiles)." CSV files in {$targetDirectory}.");

        // Find the most recent CSV file
        foreach ($csvFiles as $file) {
            $modTime = Storage::lastModified($file);
            if ($modTime > $latestTime) {
                $latestTime = $modTime;
                $latestFile = $file;
            }
        }

        if ($latestFile) {
            $this->info('Latest CSV file: '.basename($latestFile));
            $this->info('Last modified: '.date('Y-m-d H:i:s', $latestTime));
        }

        return $latestFile;
    }

    /**
     * Get a map of existing CPRs for faster lookup
     *
     * @return array
     */
    protected function getExistingCprMap()
    {
        // Get all existing CPRs and their IDs for efficient lookups
        return Caller::select('id', 'cpr', 'hits')->get()
            ->keyBy('cpr')
            ->toArray();
    }

    /**
     * Process a single record into a normalized format
     *
     * @param  array  $record
     * @param  array  $existingCprMap
     * @return array
     */
    protected function processRecord($record, $existingCprMap)
    {
        // Process last_hit field
        $lastHit = null;
        if (! empty($record['last_hit']) && $record['last_hit'] !== 'active') {
            try {
                $lastHit = Carbon::parse($record['last_hit'])->toDateTimeString();
            } catch (\Exception $e) {
                $lastHit = null;
            }
        }

        // Get CPR from record and ensure it doesn't exceed 50 characters
        $cpr = $record['CPR'] ?? $record['cpr'] ?? null;

        // Skip records without CPR or generate random one
        if (empty($cpr)) {
            $cpr = Str::random(9);

            return ['action' => 'skip', 'data' => null];
        } else {
            // Truncate CPR to 50 characters
            $cpr = Str::limit($cpr, 50, '');
        }

        // Get phone and ensure it doesn't exceed the database limit
        $phone = $record['Phone'] ?? $record['phone'] ?? '';
        $phone = Str::limit($phone, 255, '');

        // Prepare record data
        $callerData = [
            'cpr' => $cpr,
            'name' => $record['Name'] ?? $record['name'] ?? '',
            'phone' => $phone,
            'hits' => intval($record['Hits'] ?? $record['hits'] ?? 0),
            'last_hit' => $lastHit,
            'status' => $record['Status'] ?? $record['status'] ?? 'active',
            'notes' => $record['Notes'] ?? $record['notes'] ?? null,
            'is_family' => (bool) ($record['is_family'] ?? false),
            'is_winner' => $record['is_winner'] === 'Yes' || $record['is_winner'] === '1' || $record['is_winner'] === true,
            'updated_at' => now()->toDateTimeString(),
        ];

        // Add created_at for new records
        if (! isset($existingCprMap[$cpr])) {
            $callerData['created_at'] = $record['created_at'] ?? now()->toDateTimeString();
            $action = 'insert';
        } else {
            // Determine if update is needed
            $existingHits = $existingCprMap[$cpr]['hits'] ?? 0;
            $action = ($existingHits != $callerData['hits']) ? 'update' : 'skip';
        }

        return [
            'action' => $action,
            'data' => $callerData,
        ];
    }

    /**
     * Process a batch of records using bulk operations
     *
     * @param  array  $batch
     * @param  array  $existingCprMap
     * @return array
     */
    protected function processBatch($batch, $existingCprMap)
    {
        $inserted = 0;
        $updated = 0;

        if (empty($batch)) {
            return ['inserted' => 0, 'updated' => 0];
        }

        // Separate records into new and existing based on CPR
        $newRecords = [];
        $updateRecords = [];

        foreach ($batch as $record) {
            if (! isset($existingCprMap[$record['cpr']])) {
                $newRecords[] = $record;
            } else {
                $updateRecords[] = $record;
            }
        }

        // Use transactions for better performance
        DB::beginTransaction();

        try {
            // Bulk insert new records
            if (! empty($newRecords)) {
                Caller::insert($newRecords);
                $inserted = count($newRecords);
            }

            // Bulk update existing records - use upsert for efficiency
            if (! empty($updateRecords)) {
                foreach ($updateRecords as $record) {
                    $callerId = $existingCprMap[$record['cpr']]['id'] ?? null;
                    if ($callerId) {
                        Caller::where('id', $callerId)->update($record);
                        $updated++;
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return [
            'inserted' => $inserted,
            'updated' => $updated,
        ];
    }
}
