<?php

namespace Database\Seeders;

use App\Models\Caller;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class CallerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Skip if database already has data
        if (Caller::count() > 0) {
            $this->command->info('Callers table already has data. Skipping seed.');

            return;
        }

        // Import from CSV file
        $csvPath = database_path('seeders/data/callers_seed.csv');

        if (! file_exists($csvPath)) {
            $this->command->warn("Seed CSV not found at: {$csvPath}");
            $this->command->info('Creating empty CSV file for future use.');

            // Ensure directory exists
            $dir = dirname($csvPath);
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            // Create empty CSV with headers
            file_put_contents($csvPath, "Name,Phone,CPR,Status,Is Winner,Hits,Last Hit,Created At,Updated At\n");

            return;
        }

        $this->command->info("Importing callers from CSV: {$csvPath}");

        $csvData = array_map('str_getcsv', file($csvPath));
        $headers = array_shift($csvData); // Remove header row

        if (empty($csvData)) {
            $this->command->info('CSV file is empty. No callers to import.');

            return;
        }

        $imported = 0;
        $skipped = 0;

        foreach ($csvData as $row) {
            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            $data = array_combine($headers, $row);

            try {
                // Map CSV columns to database columns
                Caller::create([
                    'name' => $data['Name'] ?? null,
                    'phone' => $data['Phone'] ?? null,
                    'cpr' => $data['CPR'] ?? null,
                    'status' => $data['Status'] ?? 'active',
                    'is_winner' => filter_var($data['Is Winner'] ?? 0, FILTER_VALIDATE_BOOLEAN),
                    'hits' => (int) ($data['Hits'] ?? 0),
                    'last_hit' => ! empty($data['Last Hit']) ? $data['Last Hit'] : null,
                    'created_at' => ! empty($data['Created At']) ? $data['Created At'] : now(),
                    'updated_at' => ! empty($data['Updated At']) ? $data['Updated At'] : now(),
                ]);

                $imported++;
            } catch (\Exception $e) {
                $this->command->error("Failed to import caller: {$data['Name']} - {$e->getMessage()}");
                $skipped++;
                Log::error('CallerSeeder import error', [
                    'data' => $data,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->command->info("Imported {$imported} callers from CSV.");

        if ($skipped > 0) {
            $this->command->warn("Skipped {$skipped} callers due to errors.");
        }
    }
}
