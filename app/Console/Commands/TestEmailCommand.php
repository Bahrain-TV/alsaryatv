<?php

namespace App\Console\Commands;

use App\Mail\AdminWinnerNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmailCommand extends Command
{
    protected $signature = 'test:email
                            {email=aldoyh@gmail.com : Email address to send test to}
                            {--type=admin : Email type: admin (for admin notification)}';

    protected $description = 'Send test email to verify SMTP configuration';

    public function handle()
    {
        $email = $this->argument('email');
        $type = $this->option('type');

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

            Mail::to($email)->send(new AdminWinnerNotification(
                winners: $testWinners,
                announcement: 'هذا بريد اختباري لتجربة نظام إشعارات الفائزين. البيانات المعروضة تجريبية وليست حقيقية.'
            ));

            $this->info('✅ Email sent successfully!');
            $this->line('');
            $this->comment('📬 Check your inbox at: ' . $email);
            $this->comment('💬 Also check spam folder if not in inbox');
            $this->line('');
            $this->info('ℹ️  Current Mail Configuration:');
            $this->line('   Mailer: ' . config('mail.default'));
            $this->line('   From: ' . config('mail.from.address'));
            $this->line('   Email Type: Admin Winner Notification (Recipients: Admin)');
            $this->line('');

            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Failed to send email!");
            $this->error("Error: {$e->getMessage()}");
            $this->line('');
            $this->comment('🔧 Troubleshooting:');
            $this->comment('   1. Check SMTP settings in .env file');
            $this->comment('   2. Verify MAIL_MAILER is set to "smtp" (not "log")');
            $this->comment('   3. Confirm SMTP credentials are correct');
            $this->comment('   4. Check server firewall allows outbound SMTP (port 587 or 465)');
            $this->line('');
            $this->comment('ℹ️  Current Mail Configuration:');
            $this->line('   Mailer: ' . config('mail.default'));
            $this->line('   Host: ' . config('mail.mailers.smtp.host'));
            $this->line('   Port: ' . config('mail.mailers.smtp.port'));
            $this->line('');

            return 1;
        }
    }
}
