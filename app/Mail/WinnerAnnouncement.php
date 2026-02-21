<?php

namespace App\Mail;

use App\Models\Caller;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class WinnerAnnouncement extends Mailable
{
    use Queueable, SerializesModels;

    protected Collection $winners;

    /**
     * Create a new message instance.
     * This email is sent to ADMINS ONLY with all selected winners.
     */
    public function __construct(Collection|null $winnersList = null)
    {
        // If winners list provided, use it. Otherwise, fetch all current winners.
        $this->winners = $winnersList ?? Caller::where('is_winner', true)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'name', 'cpr', 'phone', 'hits', 'created_at', 'updated_at']);
    }

    /**
     * Get the message envelope.
     * Email intended for ADMINS ONLY
     */
    public function envelope(): Envelope
    {
        $adminEmails = config('alsarya.admin_emails') ?? [
            config('mail.from.address') ?? 'noreply@alsarya.tv',
        ];

        $winnerCount = $this->winners->count();

        return new Envelope(
            to: $adminEmails,
            subject: "📋 تقرير الفائزين - برنامج السارية (عدد الفائزين: {$winnerCount})",
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
                'winners' => $this->winners,
                'winners_count' => $this->winners->count(),
                'total_hits' => $this->winners->sum('hits'),
                'generated_at' => now()->locale('ar')->translatedFormat('j F Y H:i'),
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
