<?php

namespace App\Mail;

use App\Models\SupportRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public SupportRequest $supportRequest) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New contact message: '.($this->supportRequest->subject ?: 'Website contact form'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.contact-message-received',
        );
    }
}
