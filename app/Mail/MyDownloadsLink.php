<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MyDownloadsLink extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $email, public string $url) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Niagara Inde Apps downloads',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.my-downloads-link',
        );
    }
}
