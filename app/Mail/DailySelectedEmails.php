<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailySelectedEmails extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public array $selectedCallers,
        public int $totalCount,
        public Carbon $reportDate,
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = "🎉 الأسماء المختارة اليوم - ".
            $this->reportDate->locale('ar')->translatedFormat('j F Y').' | برنامج السارية';

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.daily-selected-emails',
            with: [
                'selectedCallers' => $this->selectedCallers,
                'totalCount' => $this->totalCount,
                'reportDate' => $this->reportDate,
                'formattedDate' => $this->reportDate->locale('ar')->translatedFormat('j F Y'),
                'dayName' => $this->reportDate->locale('ar')->translatedFormat('l'),
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
