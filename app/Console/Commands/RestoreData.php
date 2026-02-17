<?php

namespace App\Console\Commands;

use App\Models\Caller;
use Illuminate\Console\Command;

class RestoreData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'restore:data {--file=} {--confirm}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore caller data from CSV backup. Use --confirm to skip confirmation.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->line('');
        $this->warn('╔══════════════════════════════════════╗');
        $this->warn('║  AlSarya TV Data Restore Tool        ║');
        $this->warn('║  ⚠️  WARNING: DESTRUCTIVE OPERATION  ║');
        $this->warn('╚══════════════════════════════════════╝');
        $this->line('');

        // List available backups if no file specified
        if (! $this->option('file')) {
            $this->listAvailableBackups();

            return;
        }

        $file = $this->option('file');
        $confirm = $this->option('confirm');

        // Verify file exists
        if (! file_exists($file)) {
            $this->error("File not found: {$file}");

            return;
        }

        $this->info("Restore file: {$file}");
        $this->info('File size: '.human_filesize(filesize($file)));

        // Count records in CSV
        $records = count(file($file)) - 1; // Subtract header
        $this->info("Records to restore: {$records}");
        $this->line('');

        // Confirm operation
        if (! $confirm) {
            $this->warn('This will:');
            $this->line('  1. Truncate the callers table');
            $this->line('  2. Import all data from CSV');
            $this->line('  3. Update hit counters and statuses');
            $this->line('');

            if (! $this->confirm('Do you want to proceed with the restore?', false)) {
                $this->info('Restore cancelled.');

                return;
            }

            if (! $this->confirm('Are you absolutely sure? This cannot be undone!', false)) {
                $this->error('Restore cancelled by user.');

                return;
            }
        }

        $this->restoreFromCsv($file);
    }

    /**
     * List available backup files
     */
    private function listAvailableBackups()
    {
        $backupDir = storage_path('backups');

        if (! is_dir($backupDir)) {
            $this->error("Backup directory not found: {$backupDir}");

            return;
        }

        $files = glob("{$backupDir}/callers_backup_*.csv");
        if (empty($files)) {
            $this->error('No backup files found in: '.$backupDir);

            return;
        }

        // Sort by modification time (newest first)
        usort($files, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        $this->info('Available backup files (newest first):');
        $this->line('');

        $count = 0;
        foreach ($files as $file) {
            $count++;
            $filename = basename($file);
            $size = human_filesize(filesize($file));
            $date = date('Y-m-d H:i:s', filemtime($file));

            $records = count(file($file)) - 1;

            if ($count === 1) {
                $this->info("  → {$filename} ({$size}, {$records} records)");
                $this->line("    Created: {$date}");
            } else {
                $this->line("  → {$filename} ({$size}, {$records} records)");
                $this->line("    Created: {$date}");
            }

            $this->line('');
        }

        $this->info('To restore from a file, run:');
        $this->comment("  php artisan restore:data --file=storage/backups/{basename($files[0])}");
        $this->line('');
    }

    /**
     * Restore callers from CSV backup file
     */
    private function restoreFromCsv($filepath)
    {
        $this->line('');
        $this->info('Starting restore process...');
        $this->line('');

        try {
            // Open CSV file
            $handle = fopen($filepath, 'r');
            if (! $handle) {
                $this->error("Cannot open file: {$filepath}");

                return;
            }

            // Read header
            $header = fgetcsv($handle);
            if (! $header) {
                $this->error('Invalid CSV format: no header row');
                fclose($handle);

                return;
            }

            $this->info('Step 1: Truncating callers table...');
            Caller::truncate();
            $this->info('✓ Table cleared');

            $this->line('');
            $this->info('Step 2: Importing caller data...');

            $imported = 0;
            $errors = 0;
            $bar = $this->output->createProgressBar();

            while (($row = fgetcsv($handle)) !== false) {
                try {
                    // Map CSV columns to model attributes
                    $data = array_combine($header, $row);

                    // Parse boolean values
                    $isWinner = strtolower($data['Is Winner']) === 'yes' || $data['Is Winner'] === '1';

                    // Create caller
                    Caller::create([
                        'id' => $data['ID'],
                        'cpr' => $data['CPR'],
                        'phone' => $data['Phone'],
                        'name' => $data['Name'],
                        'hits' => (int) $data['Hits'],
                        'status' => $data['Status'],
                        'is_winner' => $isWinner,
                        'ip_address' => $data['IP Address'] ?? null,
                        'created_at' => $data['Created At'] ?? now(),
                        'updated_at' => $data['Updated At'] ?? now(),
                    ]);

                    $imported++;
                    $bar->advance();
                } catch (\Exception $e) {
                    $errors++;
                    $this->error("Row {$imported}: ".$e->getMessage());
                }
            }

            $bar->finish();
            fclose($handle);

            $this->line('');
            $this->line('');
            $this->info('Step 3: Verifying restored data...');

            $count = Caller::count();
            $this->info("✓ Total callers in database: {$count}");

            // Calculate stats
            $winners = Caller::where('is_winner', true)->count();
            $totalHits = Caller::sum('hits');
            $avgHits = $count > 0 ? round($totalHits / $count, 2) : 0;

            $this->info("✓ Winners: {$winners}");
            $this->info("✓ Total hits: {$totalHits}");
            $this->info("✓ Average hits per caller: {$avgHits}");

            $this->line('');
            if ($errors === 0) {
                $this->info('╔══════════════════════════════════════╗');
                $this->info('║  ✓ Restore completed successfully!   ║');
                $this->info('╚══════════════════════════════════════╝');
            } else {
                $this->warn("Restore completed with {$errors} error(s)");
            }

            $this->line('');
        } catch (\Exception $e) {
            $this->error('Restore failed: '.$e->getMessage());
        }
    }
}

/**
 * Convert bytes to human-readable format
 */
if (! function_exists('human_filesize')) {
    function human_filesize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2).' '.$units[$pow];
    }
}
