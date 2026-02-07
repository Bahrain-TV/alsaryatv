<?php

namespace App\Console\Commands;

use App\Mail\WinnerAnnouncement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmailCommand extends Command
{
    protected $signature = 'test:email
                            {email=aldoyh@gmail.com : Email address to send test to}
                            {--winner-name=محمد أحمد التجريبي : Winner name for test}
                            {--prize=250 : Prize amount}';

    protected $description = 'Send test winner announcement email to verify SMTP configuration';

    public function handle()
    {
        $email = $this->argument('email');
        $winnerName = $this->option('winner-name');
        $prize = $this->option('prize');

        $this->info("📧 Sending test email to: {$email}");
        $this->line("   Winner Name: {$winnerName}");
        $this->line("   Prize Amount: {$prize} د.ب");
        $this->line('');

        try {
            Mail::to($email)->send(new WinnerAnnouncement(
                winnerName: $winnerName,
                winnerCpr: '000000000',
                prizeAmount: $prize,
                prizeDescription: 'هذا بريد اختباري لتجربة نظام البريد الإلكتروني. جميع البيانات المعروضة تجريبية وليست حقيقية.'
            ));

            $this->info('✅ Email sent successfully!');
            $this->line('');
            $this->comment('📬 Check your inbox at: ' . $email);
            $this->comment('💬 Also check spam folder if not in inbox');
            $this->line('');
            $this->info('ℹ️  Current Mail Configuration:');
            $this->line('   Mailer: ' . config('mail.default'));
            $this->line('   From: ' . config('mail.from.address'));
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
