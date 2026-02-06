<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WinnerAnnouncement extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public string $winnerName = 'الفائز الكريم',
        public string $winnerCpr = '',
        public ?string $prizeAmount = null,
        public ?string $prizeDescription = null,
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "🎉 مبروك {$this->winnerName}! أنت الفائز في برنامج السارية",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.winner-announcement',
            with: [
                'winner_name' => $this->winnerName,
                'winner_cpr' => $this->winnerCpr,
                'prize_amount' => $this->prizeAmount,
                'prize_description' => $this->prizeDescription ?? 'تهانينا! لقد ربحت جائزة حصرية من برنامج السارية.',
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
