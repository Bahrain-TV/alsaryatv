<?php

namespace Database\Seeders;

use App\Models\Caller;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Csv\Reader;

class CallerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing callers
        Caller::truncate();

        // Find the most recent CSV file in the specified directory
        $csvDirectory = 'private/exports/csv/callers';
        $csvFiles = Storage::files($csvDirectory);

        // Filter to only include CSV files
        $csvFiles = array_filter($csvFiles, function ($file) {
            return Str::endsWith($file, '.csv');
        });

        if (empty($csvFiles)) {
            $this->command->error("No CSV files found in: {$csvDirectory}");

            return;
        }

        // Sort by last modified time (most recent first)
        usort($csvFiles, function ($a, $b) {
            return Storage::lastModified($b) - Storage::lastModified($a);
        });

        // Get the most recent file
        $latestCsvFile = $csvFiles[0];

        $this->command->info("Using latest CSV file: {$latestCsvFile}");

        try {
            // Create a temporary file to work with
            $tempFile = tempnam(sys_get_temp_dir(), 'callers_');
            file_put_contents($tempFile, Storage::get($latestCsvFile));

            // Log the path of the temporary file
            Log::info("Temporary file created at: {$tempFile}");

            // Parse the CSV file using League CSV
            $csv = Reader::createFromPath($tempFile, 'r');
            $csv->setHeaderOffset(0); // The first row contains headers

            // Get total records for progress tracking
            $totalRecords = count($csv);
            $this->command->info("Found {$totalRecords} records to import");

            $records = $csv->getRecords();
            $count = 0;
            $batchSize = 100;

            foreach ($records as $record) {
                // Properly handle the last_hit field - ensure it's a valid datetime or NULL
                $lastHit = null;
                if (! empty($record['last_hit']) && $record['last_hit'] !== 'active') {
                    try {
                        // Try to parse as a datetime
                        $lastHit = Carbon::parse($record['last_hit'])->toDateTimeString();
                    } catch (\Exception $e) {
                        // If parsing fails, log and set to null
                        $lastHit = null;
                    }
                }

                // Get CPR from record and ensure it doesn't exceed the database limit
                $cpr = $record['CPR'] ?? $record['cpr'] ?? Str::random(9);
                $cpr = Str::limit($cpr, 50, ''); // Truncate to maximum 50 characters

                // Get phone and ensure it doesn't exceed the database limit
                $phone = $record['Phone'] ?? $record['phone'] ?? '';
                $phone = Str::limit($phone, 255, ''); // Truncate to standard string length

                $hits = intval($record['Hits'] ?? $record['hits'] ?? 1);

                $level = ($hits >= 200) ? 'gold' : (($hits >= 100) ? 'silver' : 'bronze');

                // Prepare caller data
                $callerData = [
                    'name' => $record['Name'] ?? $record['name'] ?? '',
                    'phone' => $phone,
                    'cpr' => $cpr,
                    'hits' => $hits,
                    'last_hit' => $lastHit,
                    'level' => $level,
                    'status' => $record['Status'] ?? $record['status'] ?? 'active',
                    'notes' => $record['Notes'] ?? $record['notes'] ?? null,
                    'is_family' => (bool) ($record['is_family'] ?? false),
                    'is_winner' => $record['is_winner'] === 'Yes' || $record['is_winner'] === '1' || $record['is_winner'] === true,
                    'created_at' => $record['created_at'] ?? now()->toDateTimeString(),
                    'updated_at' => $record['updated_at'] ?? now()->toDateTimeString(),
                ];

                // Use updateOrCreate to handle duplicates
                Caller::updateOrCreate(
                    ['cpr' => $cpr],
                    $callerData
                );

                $count++;

                // Show progress periodically
                if ($count % $batchSize === 0) {
                    $this->command->info("Processed {$count} of {$totalRecords} records");
                }
            }

            // Clean up the temporary file
            unlink($tempFile);

            $this->command->info("Successfully imported {$count} callers from CSV.");

        } catch (\Exception $e) {
            $this->command->error('Error importing CSV: '.$e->getMessage());
            Log::error('CSV import error: '.$e->getMessage(), [
                'file' => $latestCsvFile,
                'trace' => $e->getTraceAsString(),
            ]);
        }

        // Create some sample callers
        Caller::factory()->count(50)->create();

        // Create some winners
        Caller::factory()->count(10)->create([
            'is_winner' => true,
        ]);

        // Create some family members
        Caller::factory()->count(15)->create([
            'is_family' => true,
        ]);
    }
}
