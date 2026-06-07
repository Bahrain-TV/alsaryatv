<?php

namespace App\Console\Commands;

use App\Models\Caller;
use App\Services\FileCleanupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;

class ExportCallersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'callers:export {--encrypt=true : Encrypt the export file} {--path=exports : Storage path for exports}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export all callers to encrypted CSV file for backup and archival purposes';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Starting callers export...');

        try {
            $path = $this->option('path');
            $encrypt = in_array($this->option('encrypt'), ['true', '1', 'yes', 'on'], true);

            // Ensure directory exists
            if (! Storage::exists($path)) {
                Storage::makeDirectory($path);
            }

            // Generate filename with timestamp
            $timestamp = now()->format('Y-m-d_H-i-s');
            $filename = "callers_export_{$timestamp}.csv";

            // Create CSV in memory
            $csv = Writer::createFromString('');

            // Add headers
            $csv->insertOne([
                'ID',
                'Name',
                'Phone',
                'CPR',
                'Hits',
                'Status',
                'Level',
                'Winner',
                'IP Address',
                'Last Hit',
                'Notes',
                'Created At',
                'Updated At',
            ]);

            // Stream callers in chunks to prevent memory issues
            $chunk_size = 500;
            Caller::query()
                ->chunk($chunk_size, function ($callers) use ($csv): void {
                    foreach ($callers as $caller) {
                        $csv->insertOne([
                            $caller->id,
                            $caller->name,
                            $caller->phone,
                            $caller->cpr,
                            $caller->hits,
                            $caller->status,
                            $caller->level,
                            $caller->is_winner ? 'Yes' : 'No',
                            $caller->ip_address,
                            $caller->last_hit?->toDateTimeString(),
                            $caller->notes,
                            $caller->created_at->toDateTimeString(),
                            $caller->updated_at->toDateTimeString(),
                        ]);
                    }
                });

            $csvContent = $csv->toString();
            $totalRecords = Caller::count();

            // Encrypt if requested
            if ($encrypt) {
                $this->info('Encrypting export file...');
                $encryptedContent = Crypt::encryptString($csvContent);
                $filename = str_replace('.csv', '.encrypted', $filename);

                Storage::put("{$path}/{$filename}", $encryptedContent);
                $this->info("✓ Encrypted export created: {$filename}");
            } else {
                Storage::put("{$path}/{$filename}", $csvContent);
                $this->info("✓ Export created: {$filename}");
            }

            // Create manifest file with metadata
            $manifestName = "callers_export_{$timestamp}.manifest.json";
            $manifest = [
                'export_timestamp' => now()->toIso8601String(),
                'total_records' => $totalRecords,
                'file_name' => $filename,
                'encrypted' => $encrypt,
                'compression' => false,
                'status' => 'success',
                'file_size' => Storage::size("{$path}/{$filename}"),
            ];

            Storage::put("{$path}/{$manifestName}", json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            // Keep only last 30 exports (cleanup old files)
            $this->cleanupOldExports($path);

            $this->info("✓ Manifest created: {$manifestName}");
            $this->info('✓ Export completed successfully!');
            $this->line("  Total records exported: {$totalRecords}");
            $this->line("  Location: storage/{$path}/{$filename}");
            $this->line('  Encrypted: '.($encrypt ? 'Yes' : 'No'));

        } catch (\Exception $e) {
            $this->error("Export failed: {$e->getMessage()}");
            Log::error('Callers export error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->setExitCode(1);
        }
    }

    /**
     * Clean up old export files, keeping only the latest 30
     */
    private function cleanupOldExports(string $path): void
    {
        $deleted = FileCleanupService::keepLatest($path, 30, 'callers_export_');
        if ($deleted > 0) {
            $this->line("  Cleaned up {$deleted} old export file(s)");
        }
    }
}
