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
                            {--email= : Email address to send to (persists for future runs)}
                            {--set-email= : Set persistent email without sending}
                            {--add-email= : Add email to persistent list without sending}
                            {--remove-email= : Remove email from persistent list}
                            {--clear-emails : Clear all custom emails, restore defaults}
                            {--cc= : Optional CC email addresses (comma separated, persists)}
                            {--bcc= : Optional BCC email addresses (comma separated, persists)}
                            {--limit= : Number of selected names to include (persists, default: 10)}
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
     * Configuration file path for persistent settings.
     */
    protected string $configPath;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->configPath = config_path('daily-selected-emails.php');
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $startTime = microtime(true);
        $this->info('🚀 Starting daily selected names email process...');

        try {
            // Handle email management options first
            if ($this->handleEmailManagementOptions()) {
                return static::SUCCESS;
            }

            // Get the limit (from option or config)
            $limit = $this->option('limit') ? (int) $this->option('limit') : $this->getConfigValue('limit', 10);
            if ($limit < 1) {
                $limit = 10;
            }

            // Get selected callers (last N by limit)
            $selectedCallers = $this->getSelectedCallers($limit);

            // Check if we have any selections
            if ($selectedCallers->isEmpty() && ! $this->option('force')) {
                $this->warn('⚠️ No selected callers found. Use --force to send anyway.');

                return static::SUCCESS;
            }

            // Get total eligible callers count
            $totalCount = Caller::eligible()->count();

            // Parse recipients - use persistent config or command line options
            $toEmails = $this->getRecipients();
            $ccEmails = $this->option('cc') ? $this->parseEmails($this->option('cc')) : $this->getConfigValue('recipients.cc', []);
            $bccEmails = $this->option('bcc') ? $this->parseEmails($this->option('bcc')) : $this->getConfigValue('recipients.bcc', []);

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

    /**
     * Handle email management options (--set-email, --add-email, --remove-email, --clear-emails).
     * Returns true if an action was taken and command should exit.
     */
    protected function handleEmailManagementOptions(): bool
    {
        // Set email (replaces all TO recipients)
        if ($email = $this->option('set-email')) {
            $emails = $this->parseEmails($email);
            if (empty($emails)) {
                $this->error('❌ Invalid email address provided');

                return true;
            }

            $this->updateConfig(['recipients.to' => $emails]);
            $this->info('✅ Persistent email recipients set to: '.implode(', ', $emails));

            return true;
        }

        // Add email to existing recipients
        if ($email = $this->option('add-email')) {
            $emails = $this->parseEmails($email);
            if (empty($emails)) {
                $this->error('❌ Invalid email address provided');

                return true;
            }

            $current = $this->getConfigValue('recipients.to', $this->defaultRecipients);
            $newEmails = array_unique(array_merge($current, $emails));
            $this->updateConfig(['recipients.to' => $newEmails]);
            $this->info('✅ Added email(s). Current recipients: '.implode(', ', $newEmails));

            return true;
        }

        // Remove email from recipients
        if ($email = $this->option('remove-email')) {
            $emailsToRemove = $this->parseEmails($email);
            if (empty($emailsToRemove)) {
                $this->error('❌ Invalid email address provided');

                return true;
            }

            $current = $this->getConfigValue('recipients.to', $this->defaultRecipients);
            $newEmails = array_diff($current, $emailsToRemove);

            if (empty($newEmails)) {
                $this->warn('⚠️ Cannot remove all recipients. Use --clear-emails to restore defaults.');

                return true;
            }

            $this->updateConfig(['recipients.to' => array_values($newEmails)]);
            $this->info('✅ Removed email(s). Current recipients: '.implode(', ', array_values($newEmails)));

            return true;
        }

        // Clear all custom emails and restore defaults
        if ($this->option('clear-emails')) {
            $this->updateConfig(['recipients.to' => $this->defaultRecipients]);
            $this->info('✅ Restored default recipients: '.implode(', ', $this->defaultRecipients));

            return true;
        }

        // If --email is provided, persist it for future runs
        if ($email = $this->option('email')) {
            $emails = [$email];
            $this->updateConfig(['recipients.to' => $emails]);
            $this->info('📧 Email persisted for future runs: '.$email);
        }

        return false;
    }

    /**
     * Get recipients from config or use defaults.
     */
    protected function getRecipients(): array
    {
        // If --email was explicitly provided, use it (but it's already persisted in handleEmailManagementOptions)
        if ($email = $this->option('email')) {
            return [$email];
        }

        // Get from config file
        $recipients = $this->getConfigValue('recipients.to', null);

        if (! $recipients || empty($recipients)) {
            return $this->defaultRecipients;
        }

        return $recipients;
    }

    /**
     * Get a value from the config file.
     */
    protected function getConfigValue(string $key, $default = null)
    {
        if (! file_exists($this->configPath)) {
            return $default;
        }

        $config = include $this->configPath;
        $keys = explode('.', $key);
        $value = $config;

        foreach ($keys as $k) {
            if (is_array($value) && isset($value[$k])) {
                $value = $value[$k];
            } else {
                return $default;
            }
        }

        return $value;
    }

    /**
     * Update the config file with new values.
     */
    protected function updateConfig(array $updates): void
    {
        $config = file_exists($this->configPath) ? include $this->configPath : [
            'recipients' => [
                'to' => $this->defaultRecipients,
                'cc' => [],
                'bcc' => [],
            ],
            'limit' => 10,
        ];

        // Apply updates using dot notation
        foreach ($updates as $key => $value) {
            $keys = explode('.', $key);
            $configRef = &$config;

            foreach ($keys as $k) {
                if (! isset($configRef[$k])) {
                    $configRef[$k] = [];
                }
                $configRef = &$configRef[$k];
            }

            $configRef = $value;
        }

        $config['last_updated'] = now()->toISOString();

        // Write config file
        $content = "<?php\n\nreturn ".var_export($config, true).";\n";
        file_put_contents($this->configPath, $content);
    }
}
