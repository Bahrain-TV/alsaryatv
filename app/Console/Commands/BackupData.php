<?php

namespace App\Console\Commands;

use App\Models\Caller;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BackupData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:data {--type=all} {--clean=7}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup caller data and hit counters to CSV. Options: --type=all|callers|hits, --clean=days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type');
        $cleanDays = (int) $this->option('clean');

        $this->line('');
        $this->info('╔══════════════════════════════════════╗');
        $this->info('║  AlSarya TV Data Backup Manager      ║');
        $this->info('╚══════════════════════════════════════╝');
        $this->line('');

        $backupDir = storage_path('backups');
        $logsDir = storage_path('logs/hits');

        // Ensure directories exist
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        if (!is_dir($logsDir)) {
            mkdir($logsDir, 0755, true);
        }

        $this->info("Backup directory: {$backupDir}");
        $this->info("Logs directory: {$logsDir}");
        $this->line('');

        // Backup callers data
        if (in_array($type, ['all', 'callers'])) {
            $this->backupCallersData($backupDir);
        }

        // Log today's hit counters
        if (in_array($type, ['all', 'hits'])) {
            $this->logHitCounters($logsDir);
        }

        // Clean up old files
        if ($cleanDays > 0) {
            $this->cleanupOldFiles($backupDir, $cleanDays);
            $this->cleanupOldFiles($logsDir, $cleanDays);
        }

        $this->line('');
        $this->info('✓ Backup process completed successfully!');
        $this->line('');
    }

    /**
     * Backup all callers to CSV
     */
    private function backupCallersData($backupDir)
    {
        $this->info('Backing up caller data...');

        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "callers_backup_{$timestamp}.csv";
        $filepath = "{$backupDir}/{$filename}";

        $callers = Caller::all();
        $count = $callers->count();

        // Create CSV file
        $handle = fopen($filepath, 'w');
        if (!$handle) {
            $this->error("Failed to create backup file: {$filepath}");
            return;
        }

        // Write header
        fputcsv($handle, [
            'ID',
            'CPR',
            'Phone',
            'Name',
            'Hits',
            'Status',
            'Is Winner',
            'IP Address',
            'Created At',
            'Updated At'
        ]);

        // Write caller data
        foreach ($callers as $caller) {
            fputcsv($handle, [
                $caller->id,
                $caller->cpr,
                $caller->phone,
                $caller->name,
                $caller->hits,
                $caller->status,
                $caller->is_winner ? 'Yes' : 'No',
                $caller->ip_address,
                $caller->created_at,
                $caller->updated_at
            ]);
        }

        fclose($handle);

        $this->line("  ✓ Backed up {$count} callers");
        $this->info("  → {$filename}");
    }

    /**
     * Log hit counters to daily CSV
     */
    private function logHitCounters($logsDir)
    {
        $this->info('Logging hit counters...');

        $date = now()->format('Y-m-d');
        $filename = "hits_{$date}.csv";
        $filepath = "{$logsDir}/{$filename}";

        // Create header if new file
        if (!file_exists($filepath)) {
            $handle = fopen($filepath, 'w');
            fputcsv($handle, [
                'timestamp',
                'caller_id',
                'cpr',
                'name',
                'phone',
                'hits',
                'status',
                'ip_address'
            ]);
            fclose($handle);
        }

        // Append today's snapshot
        $callers = Caller::all();
        $handle = fopen($filepath, 'a');

        if (!$handle) {
            $this->error("Failed to write to log file: {$filepath}");
            return;
        }

        $timestamp = now()->toDateTimeString();
        foreach ($callers as $caller) {
            fputcsv($handle, [
                $timestamp,
                $caller->id,
                $caller->cpr,
                $caller->name,
                $caller->phone,
                $caller->hits,
                $caller->status,
                $caller->ip_address
            ]);
        }

        fclose($handle);

        $this->line("  ✓ Logged {$callers->count()} hit counters");
        $this->info("  → {$filename}");
    }

    /**
     * Clean up backup files older than specified days
     */
    private function cleanupOldFiles($dir, $days)
    {
        $this->info("Cleaning up files older than {$days} days...");

        if (!is_dir($dir)) {
            return;
        }

        $cutoff = now()->subDays($days)->timestamp;
        $deleted = 0;

        foreach (scandir($dir) as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filepath = "{$dir}/{$file}";

            if (is_file($filepath) && filemtime($filepath) < $cutoff) {
                unlink($filepath);
                $this->line("  ✓ Deleted: {$file}");
                $deleted++;
            }
        }

        if ($deleted > 0) {
            $this->info("  Removed {$deleted} old file(s)");
        } else {
            $this->line('  No old files to delete');
        }
    }
}
