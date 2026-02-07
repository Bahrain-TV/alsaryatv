<?php

namespace App\Services;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;

/**
 * MailGuard Service
 *
 * Provides environment-aware email sending with safety checks
 * to prevent accidental email sends in development environments.
 *
 * - Development (local/testing): Logs emails instead of sending
 * - Production: Sends emails normally
 */
class MailGuard
{
    /**
     * Production environments where emails should actually be sent
     */
    private const PRODUCTION_ENVIRONMENTS = ['production', 'staging'];

    /**
     * Development environments where emails should be logged only
     */
    private const DEVELOPMENT_ENVIRONMENTS = ['local', 'testing', 'development'];

    /**
     * Check if current environment is production
     */
    public static function isProduction(): bool
    {
        return in_array(app()->environment(), self::PRODUCTION_ENVIRONMENTS);
    }

    /**
     * Check if current environment is development
     */
    public static function isDevelopment(): bool
    {
        return in_array(app()->environment(), self::DEVELOPMENT_ENVIRONMENTS);
    }

    /**
     * Log email details without sending
     *
     * @param  string  $recipient  Email recipient
     * @param  Mailable  $mailable  The mailable instance
     * @param  string  $subject  Email subject
     */
    public static function logEmail(string $recipient, Mailable $mailable, string $subject = 'Email'): void
    {
        Log::channel('mail')->info('Email logged (development mode)', [
            'recipient' => $recipient,
            'subject' => $subject,
            'timestamp' => now()->toDateTimeString(),
            'environment' => app()->environment(),
        ]);
    }

    /**
     * Get all pending emails from log file (development only)
     */
    public static function getPendingEmails(): array
    {
        if (! self::isDevelopment()) {
            return [];
        }

        $logFile = storage_path('logs/mail.log');

        if (! file_exists($logFile)) {
            return [];
        }

        $emails = [];
        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            if (str_contains($line, 'Email logged')) {
                $emails[] = $line;
            }
        }

        return $emails;
    }

    /**
     * Clear development email logs
     */
    public static function clearLogs(): bool
    {
        if (! self::isDevelopment()) {
            return false;
        }

        $logFile = storage_path('logs/mail.log');

        if (file_exists($logFile)) {
            unlink($logFile);
            Log::channel('mail')->info('Mail logs cleared');

            return true;
        }

        return false;
    }

    /**
     * Get formatted email report for development
     */
    public static function getEmailReport(): array
    {
        return [
            'environment' => app()->environment(),
            'is_production' => self::isProduction(),
            'is_development' => self::isDevelopment(),
            'mail_mailer' => config('mail.default'),
            'mail_from' => config('mail.from'),
            'emails_logged' => count(self::getPendingEmails()),
            'log_file' => storage_path('logs/mail.log'),
        ];
    }
}
