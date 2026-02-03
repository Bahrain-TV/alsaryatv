<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DownForMaintenance extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The estimated downtime in seconds
     */
    public $downtimeSeconds;

    /**
     * The reason for maintenance
     */
    public $reason;

    /**
     * Create a new message instance.
     */
    public function __construct(int $downtimeSeconds = 60, ?string $reason = null)
    {
        $this->downtimeSeconds = $downtimeSeconds;
        $this->reason = $reason ?? 'scheduled maintenance';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'AlSaryaTV - Down For Maintenance',
            from: new Address('no-reply@alsarya.tv', 'AlSaryaTV System'),
            replyTo: [
                new Address('support@alsarya.tv', 'AlSaryaTV Support'),
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.maintenance.downtime',
            with: [
                'downtimeSeconds' => $this->downtimeSeconds,
                'reason' => $this->reason,
                'estimatedEndTime' => now()->addSeconds($this->downtimeSeconds)->format('H:i:s'),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
