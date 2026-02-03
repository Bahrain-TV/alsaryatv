<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class CallersCsvMail extends Mailable
{
    use SerializesModels;

    /**
     * The path to the CSV file.
     */
    protected $csvPath;

    /**
     * Additional options for the email.
     */
    protected $options;

    /**
     * Create a new message instance.
     */
    public function __construct(string $csvPath, array $options = [])
    {
        $this->csvPath = $csvPath;
        $this->afterCommit();  // Ensure mail is sent after database transaction
        $this->options = $options;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope;
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.callers-csv',
            with: [
                'date' => now()->format('Y-m-d H:i:s'),
                'recordCount' => $this->options['recordCount'] ?? null,
                'customNote' => $this->options['customNote'] ?? null,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        if (! Storage::exists($this->csvPath)) {
            throw new \RuntimeException("CSV file not found at path: {$this->csvPath}");
        }

        $filename = basename($this->csvPath);

        return [
            Attachment::fromStorage($this->csvPath)
                ->as($filename)
                ->withMime('text/csv'),
        ];
    }
}
