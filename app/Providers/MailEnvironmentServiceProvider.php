<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use App\Services\MailGuard;
use Illuminate\Support\Facades\Log;

/**
 * MailEnvironmentServiceProvider
 *
 * Handles environment-aware email sending:
 * - Development: Logs emails to storage/logs/mail.log without sending
 * - Production: Sends emails normally
 *
 * This provider listens to mail events and enforces email safety in development.
 */
class MailEnvironmentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Listen to mail sending events
        $this->app['events']->listen(MessageSending::class, function (MessageSending $event) {
            $this->handleMessageSending($event);
        });

        // Listen to mail sent events
        $this->app['events']->listen(MessageSent::class, function (MessageSent $event) {
            $this->handleMessageSent($event);
        });
    }

    /**
     * Handle email before sending
     *
     * @param MessageSending $event
     */
    private function handleMessageSending(MessageSending $event): void
    {
        $environment = $this->app->environment();

        // In development, prevent SMTP sending and log instead
        if (MailGuard::isDevelopment()) {
            // Get recipient email
            $recipients = collect($event->message->getTo())
                ->keys()
                ->implode(', ');

            // Get subject
            $subject = $event->message->getSubject() ?? 'No Subject';

            // Log the email details
            Log::channel('mail')->info('📧 Email Intercepted in Development Mode', [
                'recipient' => $recipients,
                'subject' => $subject,
                'timestamp' => now()->toDateTimeString(),
                'environment' => $environment,
                'mailer' => config('mail.default'),
            ]);

            // Log the email body (first 500 chars)
            try {
                $bodyObject = $event->message->getBody();
                $body = $bodyObject ? $bodyObject->toString() : 'No body content';
                Log::channel('mail')->debug('Email Body Preview', [
                    'preview' => substr(strip_tags($body), 0, 500) . '...',
                ]);
            } catch (\Exception $e) {
                Log::channel('mail')->debug('Email Body:', ['message' => 'Unable to extract body']);
            }

            Log::channel('mail')->info('✋ Email NOT sent (development environment) - Use `php artisan mail:show` to preview');
            Log::channel('mail')->info('════════════════════════════════════════════════════════════════');
        }

        // In production, log successful email sending
        if (MailGuard::isProduction()) {
            $recipients = collect($event->message->getTo())
                ->keys()
                ->implode(', ');

            $subject = $event->message->getSubject() ?? 'No Subject';

            Log::channel('mail')->info('📧 Email Sending (Production)', [
                'recipient' => $recipients,
                'subject' => $subject,
                'timestamp' => now()->toDateTimeString(),
            ]);
        }
    }

    /**
     * Handle email after sending
     *
     * @param MessageSent $event
     */
    private function handleMessageSent(MessageSent $event): void
    {
        // Only log in production when email actually sends
        if (MailGuard::isProduction()) {
            $recipients = collect($event->message->getTo())
                ->keys()
                ->implode(', ');

            Log::channel('mail')->info('✅ Email Successfully Sent', [
                'recipient' => $recipients,
                'timestamp' => now()->toDateTimeString(),
            ]);
        }
    }
}
