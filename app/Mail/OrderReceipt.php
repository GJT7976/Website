<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class OrderReceipt extends Mailable
{
    use Queueable, SerializesModels;

    public string $myDownloadsUrl;

    public function __construct(public Order $order)
    {
        $this->myDownloadsUrl = URL::signedRoute('my-downloads.show', ['email' => $order->customer_email]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Receipt for order {$this->order->order_number}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.order-receipt',
        );
    }
}
