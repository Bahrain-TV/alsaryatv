<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ExportAndEmailCommand extends Command
{
    protected $signature = 'app:export:email-callers 
                         {--no-clean : Do not clean existing export files}
                         {--no-compress : Disable compression of exported data (compressed by default)}
                         {--no-email : Do not send emails after export}
                         {--retry-count=3 : Number of retry attempts for failed operations}
                         {--folder= : Custom subfolder for export files}';

    protected $description = 'Export callers data with compression and send email reports';

    public function handle()
    {
        $this->info('🚀 Starting Export and Email Sequence');
        $this->newLine();

        $startTime = microtime(true);
        $retryCount = (int) $this->option('retry-count');
        $customFolder = $this->option('folder');

        try {
            // Step 1: Clean exports directory (if not disabled)
            if (! $this->option('no-clean')) {
                $this->cleanExportDirectory($customFolder);
            } else {
                $this->warn('⏭️ Skipping cleanup (--no-clean option used)');
            }

            // Step 2: Export/dump callers data
            $exportedFiles = $this->exportCallers($customFolder, $retryCount);

            if (empty($exportedFiles)) {
                $this->error('❌ No files were exported. Cannot continue with email.');

                return 1;
            }

            // Step 3: Send emails (if not disabled)
            if (! $this->option('no-email')) {
                $this->sendEmails($exportedFiles, $retryCount);
            } else {
                $this->warn('⏭️ Skipping emails (--no-email option used)');
            }

            $executionTime = round(microtime(true) - $startTime, 2);
            $this->newLine();
            $this->info("✅ All operations completed successfully in {$executionTime} seconds!");

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Command failed: '.$e->getMessage());
            Log::error('Export and email command failed: '.$e->getMessage());

            return 1;
        }
    }

    protected function cleanExportDirectory($customFolder = null)
    {
        $this->info('🧹 Cleaning export directory...');

        $path = 'private/exports/csv/callers';
        if ($customFolder) {
            $path .= "/{$customFolder}";
        }

        try {
            $files = Storage::disk('local')->files($path);

            $count = count($files);
            if ($count === 0) {
                $this->line('   Directory is already empty.');

                return;
            }

            $bar = $this->output->createProgressBar($count);
            $bar->start();

            foreach ($files as $file) {
                Storage::disk('local')->delete($file);
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info("   ✓ Removed {$count} files from export directory.");
            $this->newLine();
        } catch (\Exception $e) {
            $this->warn("   Could not clean directory: {$e->getMessage()}");
            // This is non-fatal, so we continue execution
        }
    }

    protected function exportCallers($customFolder = null, $maxRetries = 3)
    {
        $this->info('📊 Exporting callers data...');

        // Default is now WITH compression, flag disables it
        $compress = ! $this->option('no-compress');
        $command = 'callers:dump';

        // Build command with options
        if (! $compress) {
            $command .= ' --no-compress';
        }

        if ($customFolder) {
            $command .= " --folder=\"{$customFolder}\"";
        }

        $this->line("   Running: php artisan {$command}");
        $this->line('   '.($compress ? 'Compression ENABLED' : 'Compression DISABLED'));
        $this->line('   Please wait, this may take a while...');
        $this->newLine();

        // Execute with retries
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                // Show spinner during execution
                $spinner = ['⠋', '⠙', '⠹', '⠸', '⠼', '⠴', '⠦', '⠧', '⠇', '⠏'];
                $i = 0;
                $startTime = microtime(true);

                // Start a background process to show spinner
                $process = new \Symfony\Component\Process\Process(['php', 'artisan', $command]);
                $process->setTimeout(3600); // 1 hour timeout
                $process->start();

                while ($process->isRunning()) {
                    $this->output->write("\r   ".$spinner[$i % count($spinner)].' Processing... '.round(microtime(true) - $startTime, 1).'s');
                    usleep(100000);
                    $i++;
                }

                // Process completed
                if ($process->isSuccessful()) {
                    $executionTime = round(microtime(true) - $startTime, 2);
                    $this->output->write("\r   ✓ Export completed successfully in {$executionTime} seconds.      \n");

                    // Verify export files were created
                    $path = 'private/exports/csv/callers';
                    if ($customFolder) {
                        $path .= "/{$customFolder}";
                    }

                    $files = Storage::disk('local')->files($path);
                    $fileCount = count($files);

                    $this->line('   Created '.$fileCount.' export files.');

                    // Count compressed files if compression was enabled
                    if ($compress) {
                        $zipFiles = array_filter($files, function ($file) {
                            return str_ends_with($file, '.zip');
                        });

                        $this->info('   Found '.count($zipFiles).' compressed files.');
                    }

                    $this->newLine();

                    return $files;
                } else {
                    $this->error("\r   ✗ Export failed (Attempt {$attempt}/{$maxRetries}): ".$process->getErrorOutput());

                    if ($attempt < $maxRetries) {
                        $this->warn('   Retrying in 5 seconds...');
                        sleep(5);
                    } else {
                        throw new \Exception('Maximum retry attempts reached. Export failed.');
                    }
                }
            } catch (\Exception $e) {
                $this->error("\r   ✗ Export error (Attempt {$attempt}/{$maxRetries}): ".$e->getMessage());

                if ($attempt < $maxRetries) {
                    $this->warn('   Retrying in 5 seconds...');
                    sleep(5);
                } else {
                    throw new \Exception('Maximum retry attempts reached. Export failed: '.$e->getMessage());
                }
            }
        }

        return [];
    }

    protected function sendEmails($exportedFiles = [], $maxRetries = 3)
    {
        $this->info('📧 Sending emails...');

        // Find compressed files for email attachments if they exist
        $zipFiles = array_filter($exportedFiles, function ($file) {
            return str_ends_with($file, '.zip');
        });

        $hasAttachments = ! $this->option('no-compress') && count($zipFiles) > 0;

        if ($hasAttachments) {
            $this->line('   Will include '.count($zipFiles).' compressed files in emails');
            $command = 'send:emails --with-attachments';

            // Get total size of attachments to warn about potential issues
            $totalSize = 0;
            foreach ($zipFiles as $file) {
                $totalSize += Storage::disk('local')->size($file);
            }

            $sizeInMb = round($totalSize / (1024 * 1024), 2);

            if ($sizeInMb > 10) {
                $this->warn("   ⚠️  Warning: Total attachment size is {$sizeInMb}MB which may exceed email limits");
            }

            $this->line("   Total attachment size: {$sizeInMb}MB");
        } else {
            $this->line('   Sending emails without attachments');
            $command = 'send:emails';
        }

        $this->line("   Running: php artisan {$command}");

        // Execute with retries
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $exitCode = Artisan::call($command, [], $this->output);

                if ($exitCode === 0) {
                    $this->info('   ✓ Email sending completed successfully.');
                    break;
                } else {
                    $this->error("   ✗ Email sending failed with exit code: {$exitCode} (Attempt {$attempt}/{$maxRetries})");

                    if ($attempt < $maxRetries) {
                        $this->warn('   Retrying in 5 seconds...');
                        sleep(5);
                    } else {
                        throw new \Exception('Maximum retry attempts reached. Email sending failed.');
                    }
                }
            } catch (\Exception $e) {
                $this->error("   ✗ Email sending error (Attempt {$attempt}/{$maxRetries}): ".$e->getMessage());

                if ($attempt < $maxRetries) {
                    $this->warn('   Retrying in 5 seconds...');
                    sleep(5);
                } else {
                    throw new \Exception('Maximum retry attempts reached. Email sending failed: '.$e->getMessage());
                }
            }
        }

        $this->newLine();
    }
}
