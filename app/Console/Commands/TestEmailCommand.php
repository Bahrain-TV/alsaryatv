<?php

namespace App\Console\Commands;

use App\Mail\AdminWinnerNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmailCommand extends Command
{
    protected $signature = 'test:email
                            {email? : Email address to send test to (defaults to first admin email from config)}
                            {--type=admin : Email type: admin (for admin notification)}
                            {--mailer=gmail : Mailer to use: gmail, smtp, log, or failover}';

    protected $description = 'Send test email to verify SMTP configuration';

    public function handle()
    {
        $email = $this->argument('email') ?? config('alsarya.admin_emails.0', 'admin@alsarya.tv');
        $type = $this->option('type');
        $mailer = $this->option('mailer');

        // Display email configuration
        $this->displayConfiguration($mailer);
        $this->line('');

        $this->info("📧 Sending test {$type} email to: {$email}");

        try {
            // Sample test data for admin notification
            $testWinners = [
                [
                    'name' => 'محمد أحمد إبراهيم',
                    'phone' => '+973 3366 2211',
                    'cpr' => '123456789012',
                    'hits' => 5,
                    'selected_at' => now()->locale('ar')->translatedFormat('j F Y H:i'),
                ],
                [
                    'name' => 'فاطمة علي محمد',
                    'phone' => '+973 3355 4477',
                    'cpr' => '234567890123',
                    'hits' => 3,
                    'selected_at' => now()->locale('ar')->translatedFormat('j F Y H:i'),
                ],
                [
                    'name' => 'علي سالم خميس',
                    'phone' => '+973 3344 5566',
                    'cpr' => '345678901234',
                    'hits' => 7,
                    'selected_at' => now()->locale('ar')->translatedFormat('j F Y H:i'),
                ],
            ];

            Mail::mailer($mailer)->to($email)->send(new AdminWinnerNotification(
                winners: $testWinners,
                announcement: 'هذا بريد اختباري لتجربة نظام إشعارات الفائزين. البيانات المعروضة تجريبية وليست حقيقية.'
            ));

            $this->info("✅ Email sent successfully via {$mailer}!");
            $this->line('');
            $this->comment('📬 Check your inbox at: '.$email);
            $this->comment('💬 Also check spam folder if not in inbox');
            $this->line('');

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Failed to send email!');
            $this->error("Error: {$e->getMessage()}");
            $this->line('');
            $this->comment('🔧 Troubleshooting:');
            $this->comment('   1. Check Gmail credentials in .env (MAIL_USERNAME, MAIL_PASSWORD)');
            $this->comment('   2. Verify Gmail App Password (not regular password)');
            $this->comment('   3. Enable 2-FA on your Google Account');
            $this->comment('   4. Check server firewall allows outbound SMTP (port 587)');
            $this->comment('   5. Verify firewall rules: https://myaccount.google.com/apppasswords');
            $this->line('');
            $this->comment('ℹ️  Current Mail Configuration:');
            $this->line('   Default Mailer: '.config('mail.default'));
            $this->line('   Gmail Host: '.config('mail.mailers.gmail.host'));
            $this->line('   Gmail Port: '.config('mail.mailers.gmail.port'));
            $this->line('   Gmail Username: '.config('mail.mailers.gmail.username'));
            $this->line('');

            return 1;
        }
    }

    private function displayConfiguration(string $mailer): void
    {
        $this->info('⚙️  Email Configuration:');
        $this->line('   Primary Mailer: Gmail');
        $this->line('   Using: '.$mailer);
        $this->line('');

        if ($mailer === 'gmail') {
            $this->comment('📧 Gmail Configuration:');
            $this->line('   Host: '.config('mail.mailers.gmail.host'));
            $this->line('   Port: '.config('mail.mailers.gmail.port'));
            $this->line('   Encryption: '.config('mail.mailers.gmail.encryption'));
            $this->line('   Username: '.config('mail.mailers.gmail.username'));
        } elseif ($mailer === 'smtp') {
            $this->comment('📧 SMTP Configuration:');
            $this->line('   Host: '.config('mail.mailers.smtp.host'));
            $this->line('   Port: '.config('mail.mailers.smtp.port'));
            $this->line('   Encryption: '.config('mail.mailers.smtp.encryption'));
            $this->line('   Username: '.config('mail.mailers.smtp.username'));
        }

        $this->line('   From Address: '.config('mail.from.address'));
        $this->line('   From Name: '.config('mail.from.name'));
    }
}
