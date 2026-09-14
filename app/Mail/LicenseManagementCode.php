<?php

namespace App\Mail;

use App\Models\License;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * §10: the one-time code for the self-service device-management flow.
 * Only ever sent to the license's own customer_email (see
 * LicenseService::requestManagementCode) — never to whatever address a
 * requester typed in.
 */
class LicenseManagementCode extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public License $license, public string $code) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your license management code');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.license-management-code');
    }
}
