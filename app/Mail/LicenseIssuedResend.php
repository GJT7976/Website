<?php

namespace App\Mail;

use App\Models\License;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * §23 "Resend license email" — reads license_key_encrypted (never the
 * hash) so support can recover a lost key without the customer buying
 * again. Only ever triggered from Admin\LicenseController::resendEmail().
 */
class LicenseIssuedResend extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public License $license) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Your {$this->license->app->name} license key");
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.license-issued-resend');
    }
}
