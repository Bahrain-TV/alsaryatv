<?php

namespace App\Console\Commands;

use App\Mail\DailySelectedEmails;
use App\Models\Caller;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendDailySelectedEmailsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send:daily-selected-emails
                            {--email= : Optional email address to send to (overrides default)}
                            {--cc= : Optional CC email addresses (comma separated)}
                            {--bcc= : Optional BCC email addresses (comma separated)}
                            {--limit=10 : Number of selected names to include (default: 10)}
                            {--force : Force send even if no selections today}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily email with the last selected names from the Selection dashboard';

    /**
     * Default recipients for the daily selected emails report.
     */
    protected array $defaultRecipients = [
        'aldoyh.info@gmail.com',
        'alsaryatv@gmail.com',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $startTime = microtime(true);
        $this->info('🚀 Starting daily selected names email process...');

        try {
            // Get the limit
            $limit = (int) $this->option('limit');
            if ($limit < 1) {
                $limit = 10;
            }

            // Get selected callers (last 10 by default)
            $selectedCallers = $this->getSelectedCallers($limit);

            // Check if we have any selections
            if ($selectedCallers->isEmpty() && ! $this->option('force')) {
                $this->warn('⚠️ No selected callers found. Use --force to send anyway.');

                return static::SUCCESS;
            }

            // Get total eligible callers count
            $totalCount = Caller::eligible()->count();

            // Parse recipients
            $toEmails = $this->option('email') ? [$this->option('email')] : $this->defaultRecipients;
            $ccEmails = $this->parseEmails($this->option('cc'));
            $bccEmails = $this->parseEmails($this->option('bcc'));

            $this->info("📧 Sending to: ".implode(', ', $toEmails));

            if (! empty($ccEmails)) {
                $this->info('CC: '.implode(', ', $ccEmails));
            }

            if (! empty($bccEmails)) {
                $this->info('BCC: '.implode(', ', $bccEmails));
            }

            // Send the email
            $this->sendEmail($selectedCallers, $totalCount, $toEmails, $ccEmails, $bccEmails);

            $executionTime = round(microtime(true) - $startTime, 2);
            $this->info("✅ Process completed in {$executionTime} seconds");

            return static::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error: '.$e->getMessage());
            $this->error('Stack trace: '.$e->getTraceAsString());

            return static::FAILURE;
        }
    }

    /**
     * Get the last selected callers.
     */
    protected function getSelectedCallers(int $limit): \Illuminate\Support\Collection
    {
        return Caller::selected()
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get()
            ->map(fn (Caller $caller) => [
                'id' => $caller->id,
                'name' => $caller->name,
                'phone' => $caller->phone,
                'cpr' => $caller->cpr,
                'hits' => $caller->hits,
                'status' => $caller->status,
                'selected_at' => $caller->updated_at,
            ]);
    }

    /**
     * Send the email with selected callers.
     */
    protected function sendEmail(
        \Illuminate\Support\Collection $selectedCallers,
        int $totalCount,
        array $toEmails,
        array $ccEmails,
        array $bccEmails
    ): void {
        $reportDate = Carbon::now();

        Mail::to($toEmails)
            ->cc($ccEmails)
            ->bcc($bccEmails)
            ->send(new DailySelectedEmails(
                $selectedCallers->toArray(),
                $totalCount,
                $reportDate
            ));

        $this->info('✅ Email sent successfully!');
        $this->info('📊 Included '.count($selectedCallers).' selected name(s)');
    }

    /**
     * Parse comma-separated email addresses.
     */
    protected function parseEmails(?string $emails): array
    {
        if (! $emails) {
            return [];
        }

        return array_filter(array_map('trim', explode(',', $emails)));
    }
}
