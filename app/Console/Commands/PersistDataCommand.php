<?php

namespace App\Console\Commands;

use App\Models\Caller;
use App\Services\CsvExportService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PersistDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:persist-data
                            {--export-csv : Export data to CSV for backup}
                            {--verify : Verify data integrity}
                            {--full-backup : Create a full database backup}
                            {--d|debug : Show detailed debug information}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure data persistency across production environments by backing up and verifying critical data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('╔════════════════════════════════════════════════════════╗');
        $this->info('║         Data Persistence Command Started              ║');
        $this->info('╚════════════════════════════════════════════════════════╝');

        try {
            // Step 1: Verify data integrity
            if ($this->option('verify') || true) { // Always verify
                $this->verifyDataIntegrity();
            }

            // Step 2: Export data to CSV for backup
            if ($this->option('export-csv') || true) { // Always export
                $this->exportDataToBackup();
            }

            // Step 3: Verify database backups exist
            $this->verifyBackupStructure();

            // Step 4: Log persistence metrics
            $this->logPersistenceMetrics();

            $this->info('');
            $this->info('✓ Data persistence check completed successfully!');
            $this->info('');

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Data persistence check failed: ' . $e->getMessage());
            Log::error('Data persistence check failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return self::FAILURE;
        }
    }

    /**
     * Verify data integrity
     */
    protected function verifyDataIntegrity(): void
    {
        $this->info('');
        $this->info('→ Verifying data integrity...');

        try {
            // Check database connection
            DB::connection()->getPdo();
            $this->line('  ✓ Database connection verified');

            // Count total callers
            $totalCallers = Caller::count();
            $this->line("  ✓ Total callers in database: {$totalCallers}");

            // Check for data anomalies
            $nullCprCount = Caller::whereNull('cpr')->count();
            $nullPhoneCount = Caller::whereNull('phone')->count();
            $nullNameCount = Caller::whereNull('name')->count();

            if ($nullCprCount > 0) {
                $this->warn("  ⚠ Found {$nullCprCount} callers with NULL CPR");
            }
            if ($nullPhoneCount > 0) {
                $this->error("  ✗ Found {$nullPhoneCount} callers with NULL phone (critical)");
            }
            if ($nullNameCount > 0) {
                $this->error("  ✗ Found {$nullNameCount} callers with NULL name (critical)");
            }

            // Check for duplicate CPRs
            $duplicateCprs = DB::table('callers')
                ->whereNotNull('cpr')
                ->groupBy('cpr')
                ->havingRaw('count(*) > 1')
                ->count();

            if ($duplicateCprs > 0) {
                $this->warn("  ⚠ Found {$duplicateCprs} duplicate CPR values");
            }

            // Check winner statistics
            $winners = Caller::where('is_winner', true)->count();
            $this->line("  ✓ Total winners marked: {$winners}");

            // Check family members
            $family = Caller::where('is_family', true)->count();
            $this->line("  ✓ Total family members marked: {$family}");

            $this->info('✓ Data integrity verification completed');

        } catch (\Exception $e) {
            $this->error("✗ Data integrity verification failed: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Export data to backup
     */
    protected function exportDataToBackup(): void
    {
        $this->info('');
        $this->info('→ Exporting data to CSV backup...');

        try {
            $callers = Caller::all();
            $csvService = new CsvExportService();
            $filename = $csvService->generate($callers);

            if ($filename) {
                $this->line("  ✓ CSV backup created: {$filename}");
            } else {
                throw new \Exception('Failed to generate CSV backup');
            }

            // Also create a timestamped backup
            $this->createTimestampedBackup($callers);

            $this->info('✓ Data export completed');

        } catch (\Exception $e) {
            $this->error("✗ Data export failed: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Create a timestamped backup file
     */
    protected function createTimestampedBackup($callers): void
    {
        try {
            $timestamp = now()->format('Y-m-d_H-i-s');
            $backupDir = 'backups/callers';

            // Create backup directory if it doesn't exist
            if (!Storage::exists($backupDir)) {
                Storage::makeDirectory($backupDir, 0755, true);
            }

            // Generate CSV content
            $csv = "Name,Phone,CPR,Status,Is Winner,Is Family,Hits,Last Hit,Created At,Updated At\n";

            foreach ($callers as $caller) {
                $csv .= sprintf(
                    "\"%s\",\"%s\",\"%s\",\"%s\",%d,%d,%d,\"%s\",\"%s\",\"%s\"\n",
                    str_replace('"', '""', $caller->name ?? ''),
                    str_replace('"', '""', $caller->phone ?? ''),
                    str_replace('"', '""', $caller->cpr ?? ''),
                    str_replace('"', '""', $caller->status ?? 'active'),
                    $caller->is_winner ? 1 : 0,
                    $caller->is_family ? 1 : 0,
                    $caller->hits ?? 0,
                    str_replace('"', '""', $caller->last_hit?->format('Y-m-d H:i:s') ?? ''),
                    str_replace('"', '""', $caller->created_at?->format('Y-m-d H:i:s') ?? ''),
                    str_replace('"', '""', $caller->updated_at?->format('Y-m-d H:i:s') ?? '')
                );
            }

            $filename = "callers_backup_{$timestamp}.csv";
            $filepath = "{$backupDir}/{$filename}";

            Storage::put($filepath, $csv);
            $this->line("  ✓ Timestamped backup created: {$filename}");

            // Keep only last 30 backups
            $this->cleanOldBackups($backupDir, 30);

        } catch (\Exception $e) {
            $this->warn("⚠ Failed to create timestamped backup: {$e->getMessage()}");
        }
    }

    /**
     * Clean old backup files (keep only N most recent)
     */
    protected function cleanOldBackups(string $backupDir, int $keepCount): void
    {
        try {
            $files = Storage::files($backupDir);

            // Sort by modification time (newest first)
            usort($files, function ($a, $b) {
                return Storage::lastModified($b) <=> Storage::lastModified($a);
            });

            // Remove files beyond the keep count
            $filesToDelete = array_slice($files, $keepCount);

            foreach ($filesToDelete as $file) {
                Storage::delete($file);
                $this->debugLog("Deleted old backup: {$file}");
            }

            if (count($filesToDelete) > 0) {
                $this->line("  ✓ Cleaned up " . count($filesToDelete) . " old backups (kept {$keepCount})");
            }

        } catch (\Exception $e) {
            $this->warn("⚠ Failed to clean old backups: {$e->getMessage()}");
        }
    }

    /**
     * Verify backup structure and storage
     */
    protected function verifyBackupStructure(): void
    {
        $this->info('');
        $this->info('→ Verifying backup structure...');

        try {
            // Check if backup directories exist
            $backupDirs = [
                'backups/callers',
                'exports/callers',
            ];

            foreach ($backupDirs as $dir) {
                if (!Storage::exists($dir)) {
                    Storage::makeDirectory($dir, 0755, true);
                    $this->line("  ✓ Created backup directory: {$dir}");
                } else {
                    $fileCount = count(Storage::files($dir));
                    $this->line("  ✓ Backup directory verified: {$dir} ({$fileCount} files)");
                }
            }

            // Check available disk space
            $diskSpace = disk_free_space(storage_path());
            $diskSpaceGb = $diskSpace / (1024 ** 3);
            $this->line("  ✓ Available disk space: {$diskSpaceGb:.2f} GB");

            if ($diskSpaceGb < 1) {
                $this->error("  ✗ Warning: Less than 1GB available disk space!");
            }

            $this->info('✓ Backup structure verified');

        } catch (\Exception $e) {
            $this->error("✗ Backup structure verification failed: {$e->getMessage()}");
        }
    }

    /**
     * Log persistence metrics
     */
    protected function logPersistenceMetrics(): void
    {
        $this->info('');
        $this->info('→ Logging persistence metrics...');

        try {
            $metrics = [
                'timestamp' => now(),
                'total_callers' => Caller::count(),
                'total_winners' => Caller::where('is_winner', true)->count(),
                'total_family' => Caller::where('is_family', true)->count(),
                'database' => config('database.default'),
                'environment' => app()->environment(),
                'disk_space_available_gb' => disk_free_space(storage_path()) / (1024 ** 3),
            ];

            Log::info('Data persistence metrics', $metrics);

            $this->line('  ✓ Metrics logged:');
            foreach ($metrics as $key => $value) {
                if (is_numeric($value)) {
                    $this->line("    - {$key}: " . number_format($value, 2));
                } else {
                    $this->line("    - {$key}: {$value}");
                }
            }

            $this->info('✓ Metrics logged successfully');

        } catch (\Exception $e) {
            $this->warn("⚠ Failed to log persistence metrics: {$e->getMessage()}");
        }
    }

    /**
     * Debug log helper
     */
    protected function debugLog(string $message): void
    {
        if ($this->option('debug')) {
            $this->info('[DEBUG] ' . $message);
        }
    }
}
