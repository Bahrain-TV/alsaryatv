<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SendCallersCsvCommand extends Command
{
    protected $signature = 'app:send:callers-csv 
                           {email? : The email address to send the CSV to}
                           {--cc= : Optional CC email addresses (comma separated)}
                           {--bcc= : Optional BCC email addresses (comma separated)}
                           {--subject= : Custom email subject}
                           {--note= : Additional note to include in the email}
                           {--family-only : Export only family callers}
                           {--individual-only : Export only individual callers}';

    protected $description = 'Send a CSV export of callers to specified email address';

    /**
     * Default recipients when no email is provided
     */
    protected $defaultRecipients = [
        'aldoyh.info@gmail.com',
        'alsaryatv@gmail.com',
    ];

    /**
     * Execute the console command
     */
    public function handle()
    {
        $startTime = microtime(true);
        $this->info('🚀 Starting callers CSV export & email process');

        try {
            // Parse recipients
            $toEmails = $this->argument('email') ? [$this->argument('email')] : $this->defaultRecipients;
            $ccEmails = $this->parseEmails($this->option('cc'));
            $bccEmails = $this->parseEmails($this->option('bcc'));

            // Parse options
            $subject = $this->option('subject') ?: 'Callers Data Export - '.now()->format('Y-m-d H:i');
            $note = $this->option('note');
            $familyOnly = $this->option('family-only');
            $individualOnly = $this->option('individual-only');

            if ($familyOnly && $individualOnly) {
                $this->error('Cannot use both --family-only and --individual-only options together');

                return 1;
            }

            // Build query directly to avoid ORM events
            $query = DB::table('callers');

            if ($familyOnly) {
                $query->where('is_family', true);
                $this->info('Exporting family callers only');
            } elseif ($individualOnly) {
                $query->where('is_family', false);
                $this->info('Exporting individual callers only');
            }

            // Count records
            $totalRecords = $query->count();
            if ($totalRecords === 0) {
                $this->warn('No callers found matching the criteria');

                return 0;
            }

            $this->info("Found {$totalRecords} caller records");

            // Generate CSV file
            $csvPath = $this->generateCsv($query, $familyOnly, $individualOnly);
            if (! $csvPath) {
                $this->error('Failed to generate CSV file');

                return 1;
            }

            $fileSize = Storage::size($csvPath);
            $this->info("CSV file generated: {$csvPath} (".$this->formatBytes($fileSize).')');

            // Send email
            $emailStatus = $this->sendEmail($csvPath, $toEmails, $ccEmails, $bccEmails, $subject, $note, $totalRecords);
            if (! $emailStatus) {
                $this->error('Failed to send email');

                return 1;
            }

            $executionTime = round(microtime(true) - $startTime, 2);
            $this->info("✅ Process completed in {$executionTime} seconds");

            return 0;

        } catch (\Exception $e) {
            $this->error('Error: '.$e->getMessage());
            $this->error('Stack trace: '.$e->getTraceAsString());

            return 1;
        }
    }

    /**
     * Parse comma-separated email addresses
     */
    protected function parseEmails(?string $emails): array
    {
        if (! $emails) {
            return [];
        }

        return array_filter(array_map('trim', explode(',', $emails)));
    }

    /**
     * Generate CSV file from query
     */
    protected function generateCsv($query, bool $familyOnly = false, bool $individualOnly = false): ?string
    {
        try {
            $timestamp = Carbon::now()->format('Y-m-d_His');
            $typeLabel = $familyOnly ? 'family' : ($individualOnly ? 'individual' : 'all');
            $filename = "callers_{$typeLabel}_{$timestamp}.csv";
            $directory = 'exports/csv';
            $path = "{$directory}/{$filename}";

            // Create directory if it doesn't exist
            Storage::makeDirectory($directory);

            // Create CSV file with headers
            $headers = ['ID', 'Name', 'Phone', 'CPR', 'Family', 'Winner', 'Hits', 'Status', 'Notes', 'Created At'];

            $handle = fopen(Storage::path($path), 'w');
            if (! $handle) {
                throw new \Exception("Could not create file: {$path}");
            }

            fputcsv($handle, $headers);

            $total = $query->count();
            $this->output->progressStart($total);

            // Use chunking to process records in batches and prevent infinite loops
            $query->orderBy('id')->chunk(1000, function ($records) use ($handle): void {
                foreach ($records as $record) {
                    fputcsv($handle, [
                        $record->id,
                        $record->name,
                        $record->phone_number,
                        $record->cpr,
                        $record->is_family ? 'Yes' : 'No',
                        $record->is_winner ? 'Yes' : 'No',
                        $record->hits,
                        $record->status,
                        $record->notes,
                        $record->created_at,
                    ]);
                    $this->output->progressAdvance(1);
                }
            });

            $this->output->progressFinish();
            fclose($handle);

            return $path;

        } catch (\Exception $e) {
            $this->error('Error generating CSV: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Send email with CSV attachment
     */
    protected function sendEmail(
        string $csvPath,
        array $toEmails,
        array $ccEmails,
        array $bccEmails,
        string $subject,
        ?string $note,
        int $recordCount
    ): bool {
        try {
            // Verify file exists before attempting to send
            if (! Storage::exists($csvPath)) {
                $this->warn("CSV file not found: {$csvPath}");

                return false;
            }

            $fullPath = Storage::path($csvPath);
            $fileSize = Storage::size($csvPath);
            $fileName = basename($csvPath);

            // Format recipient info for display
            $recipientsList = implode(', ', $toEmails);
            $this->info("Sending email to: {$recipientsList}");

            if (! empty($ccEmails)) {
                $this->info('CC: '.implode(', ', $ccEmails));
            }

            if (! empty($bccEmails)) {
                $this->info('BCC: '.implode(', ', $bccEmails));
            }

            $this->line("Attaching CSV file: {$fileName} (".$this->formatBytes($fileSize).')');

            // Create email content
            $content = "Attached is your export of {$recordCount} caller records.\n\n";

            if ($note) {
                $content .= "Note: {$note}\n\n";
            }

            $content .= 'Generated at: '.now()."\n";
            $content .= 'By: '.config('app.name').' Export Tool';

            // Use a direct approach to avoid event recursion
            $this->line('Sending email...');

            try {
                Mail::raw($content, function ($message) use ($toEmails, $ccEmails, $bccEmails, $subject, $fullPath, $fileName): void {
                    // Set recipients
                    $message->to($toEmails);

                    if (! empty($ccEmails)) {
                        $message->cc($ccEmails);
                    }

                    if (! empty($bccEmails)) {
                        $message->bcc($bccEmails);
                    }

                    $message->subject($subject);

                    // Attach the CSV file
                    if (file_exists($fullPath)) {
                        $message->attach($fullPath, [
                            'as' => $fileName,
                            'mime' => 'text/csv',
                        ]);
                    }
                });

                $this->info('✅ Email sent successfully!');
                Log::info('CSV export email sent successfully', [
                    'recipients' => $recipientsList,
                    'file' => $fileName,
                    'records' => $recordCount,
                ]);

                return true;
            } catch (\Exception $e) {
                $this->error('Mail sending error: '.$e->getMessage());

                return false;
            }
        } catch (\Exception $e) {
            $this->error('Email preparation error: '.$e->getMessage());
            Log::error('CSV email sending error', [
                'error' => $e->getMessage(),
                'file' => $csvPath ?? 'unknown',
            ]);

            return false;
        }
    }

    /**
     * Format bytes to human-readable size
     */
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
