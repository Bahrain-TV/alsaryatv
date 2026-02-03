<?php

namespace App\Console\Commands;

use App\Services\CsvExportService;
use App\Services\GoogleSheetsCallerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncCallersCommand extends Command
{
    protected $signature = 'callers:sync 
        {--force : Force sync without confirmation}
        {--export-only : Only export CSV without syncing to Google Sheets}
        {--sheet-name=Callers : Name of the sheet to sync with}';

    protected $hidden = false;

    protected $name = 'callers:sync';

    protected $aliases = [
        'callers:dumo' => 'callers:sync --export-only',
    ];

    protected $description = 'Sync callers data with Google Sheets and generate CSV export';

    protected $csvService;

    protected $sheetsService;

    public function __construct(CsvExportService $csvService)
    {
        parent::__construct();
        $this->csvService = $csvService;
    }

    public function handle()
    {
        try {
            if (! $this->option('force') && ! $this->confirm('This will sync all callers data. Continue?')) {
                $this->info('Operation cancelled.');

                return 1;
            }

            $this->info('Starting callers sync process...');

            // Initialize Google Sheets service
            if (! $this->option('export-only')) {
                $this->initializeSheetsService();
            }

            // Step 1: Generate CSV Export
            $this->info('Generating CSV export...');
            $progressBar = $this->output->createProgressBar();
            $progressBar->start();

            try {
                $callers = \App\Models\Caller::all();
                $csvPath = $this->csvService->generate($callers);

                $progressBar->finish();
                $this->newLine();
                $this->info("CSV export generated successfully at: {$csvPath}");

                // Log success
                Log::info('Callers CSV export completed', [
                    'path' => $csvPath,
                    'count' => $callers->count(),
                ]);
            } catch (\Exception $e) {
                $this->error('Failed to generate CSV export: '.$e->getMessage());
                Log::error('CSV export failed', ['error' => $e->getMessage()]);

                return 1;
            }

            // Step 2: Sync with Google Sheets if not export-only
            if (! $this->option('export-only')) {
                $this->info('Syncing with Google Sheets...');

                try {
                    // Test connection first
                    $this->sheetsService->testConnection();

                    // Perform sync
                    $result = $this->sheetsService->syncCallers();

                    // Display results
                    $this->displaySyncResults($result);

                    // Log success
                    Log::info('Google Sheets sync completed', [
                        'added_to_sheet' => count($result['added_to_sheet']),
                        'updated_in_sheet' => count($result['updated_in_sheet']),
                        'skipped' => count($result['skipped']),
                    ]);
                } catch (\Exception $e) {
                    $this->error('Failed to sync with Google Sheets: '.$e->getMessage());
                    Log::error('Google Sheets sync failed', ['error' => $e->getMessage()]);

                    return 1;
                }
            }

            $this->info('Sync process completed successfully!');

            return 0;

        } catch (\Exception $e) {
            $this->error('An unexpected error occurred: '.$e->getMessage());
            Log::error('Sync command failed', ['error' => $e->getMessage()]);

            return 1;
        }
    }

    protected function initializeSheetsService()
    {
        $this->sheetsService = new GoogleSheetsCallerService(
            config('services.google.sheets.spreadsheet_id'),
            $this->option('sheet-name'),
            storage_path('app/google-credentials.json')
        );

        // Set up backoff handler for rate limiting
        $this->sheetsService->setBackoffHandler(function ($operation) {
            $attempts = 0;
            $maxAttempts = 5;

            while ($attempts < $maxAttempts) {
                try {
                    return $operation();
                } catch (\Exception $e) {
                    if (strpos($e->getMessage(), 'Rate Limit Exceeded') !== false) {
                        $attempts++;
                        $delay = pow(2, $attempts); // Exponential backoff
                        $this->warn("Rate limit hit, waiting {$delay} seconds...");
                        sleep($delay);

                        continue;
                    }
                    throw $e;
                }
            }

            throw new \Exception('Max retry attempts reached for rate limiting');
        });
    }

    protected function displaySyncResults(array $result)
    {
        $this->info('Sync Results:');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Added to Sheet', count($result['added_to_sheet'])],
                ['Updated in Sheet', count($result['updated_in_sheet'])],
                ['Skipped (No Changes)', count($result['skipped'])],
                ['Rate Limited Retries', $result['rate_limited_retries']],
            ]
        );

        if (! empty($result['added_to_sheet'])) {
            $this->line("\nNewly Added Records:");
            foreach ($result['added_to_sheet'] as $phone) {
                $this->line("  - {$phone}");
            }
        }
    }
}
