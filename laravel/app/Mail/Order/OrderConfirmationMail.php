<?php

namespace App\Mail\Order;

use App\Models\Order\Order;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OrderConfirmationMail extends Mailable
{
    public function __construct(
        public Order $order,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            replyTo: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: 'Your Order Has Been Confirmed - #' . strtoupper(substr($this->order->uuid, 0, 8)),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order.confirmation',
            with: [
                'order' => $this->order,
                'orderNumber' => strtoupper(substr($this->order->uuid, 0, 8)),
                'customerName' => $this->order->user->name ?? 'Customer',
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
