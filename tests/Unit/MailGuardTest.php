<?php

namespace Tests\Unit;

use App\Services\MailGuard;
use Tests\TestCase;

class MailGuardTest extends TestCase
{
    public function test_is_development_returns_true_in_testing_env(): void
    {
        $this->assertTrue(MailGuard::isDevelopment());
    }

    public function test_is_production_returns_false_in_testing_env(): void
    {
        $this->assertFalse(MailGuard::isProduction());
    }

    public function test_get_pending_emails_returns_empty_array_when_no_log_file(): void
    {
        $logFile = storage_path('logs/mail.log');
        if (file_exists($logFile)) {
            unlink($logFile);
        }

        $emails = MailGuard::getPendingEmails();

        $this->assertIsArray($emails);
        $this->assertEmpty($emails);
    }

    public function test_get_pending_emails_returns_logged_entries(): void
    {
        $logFile = storage_path('logs/mail.log');
        $dir = dirname($logFile);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($logFile, "Email logged (development mode) recipient=test@test.com\n");

        $emails = MailGuard::getPendingEmails();

        $this->assertCount(1, $emails);
        $this->assertStringContainsString('Email logged', $emails[0]);

        // Cleanup
        unlink($logFile);
    }

    public function test_clear_logs_removes_log_file(): void
    {
        $logFile = storage_path('logs/mail.log');
        $dir = dirname($logFile);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($logFile, "Email logged test\n");

        $result = MailGuard::clearLogs();

        $this->assertTrue($result);
        $this->assertFileDoesNotExist($logFile);
    }

    public function test_clear_logs_returns_false_when_no_file_exists(): void
    {
        $logFile = storage_path('logs/mail.log');
        if (file_exists($logFile)) {
            unlink($logFile);
        }

        $result = MailGuard::clearLogs();

        $this->assertFalse($result);
    }

    public function test_get_email_report_returns_expected_structure(): void
    {
        $report = MailGuard::getEmailReport();

        $this->assertArrayHasKey('environment', $report);
        $this->assertArrayHasKey('is_production', $report);
        $this->assertArrayHasKey('is_development', $report);
        $this->assertArrayHasKey('mail_mailer', $report);
        $this->assertArrayHasKey('mail_from', $report);
        $this->assertArrayHasKey('emails_logged', $report);
        $this->assertArrayHasKey('log_file', $report);
    }

    public function test_get_email_report_reflects_development_state(): void
    {
        $report = MailGuard::getEmailReport();

        $this->assertTrue($report['is_development']);
        $this->assertFalse($report['is_production']);
    }
}
