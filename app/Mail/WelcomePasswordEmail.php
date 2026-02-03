<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomePasswordEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $password;

    public $name;

    public $email;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $password,
        string $name,
        string $email
    ) {
        $this->password = $password;
        $this->name = $name;
        $this->email = $email;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: env('MAIL_FROM_ADDRESS'),
            subject: 'وصلك خطاب ترحيبي',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome',
            markdown: 'emails.welcome',
            with: [
                'name' => $this->name,
                'password' => $this->password,
                'email' => $this->email,
            ],
        );
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->view('emails.welcome')
            ->with([
                'name' => $this->name,
                'password' => $this->password,
                'email' => $this->email,
                'unsubscribe_link' => config('app.frontend_url').'/unsubscribe',
                'view_in_browser_link' => config('app.frontend_url').'/view-in-browser',
            ]);
    }
}
