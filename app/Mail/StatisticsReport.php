<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class StatisticsReport extends Mailable
{
    use SerializesModels;

    protected $reportData;

    protected $reportFile;

    public $theme = 'default';

    public function __construct(string $reportData, string $reportFile)
    {
        $this->reportData = $reportData;
        $this->reportFile = $reportFile;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Statistics Report').' - '.Carbon::now()->format('Y-m-d H:i:s')
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.statistics-markdown-report',
            with: [
                'data' => $this->reportData,
                'date' => Carbon::now()->format('Y-m-d H:i:s'),
            ]
        );
    }

    public function attachments(): array
    {
        $attachments = [];

        if ($this->reportFile && Storage::exists($this->reportFile)) {
            $attachments[] = Storage::path($this->reportFile);
        }

        return $attachments;
    }
}
