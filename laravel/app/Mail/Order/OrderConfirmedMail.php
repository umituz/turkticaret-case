<?php

namespace App\Mail\Order;

use App\Helpers\Order\OrderHelper;
use App\Models\Order\Order;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Mailable class for sending order confirmed emails.
 *
 * This mailable handles the composition and sending of order confirmed
 * emails to customers. It includes order details, customer information,
 * and uses OrderHelper for consistent formatting.
 *
 * @package App\Mail\Order
 */
class OrderConfirmedMail extends Mailable
{
    /**
     * Create a new mailable instance.
     *
     * @param Order $order The confirmed order to email about
     */
    public function __construct(public Order $order) {}

    /**
     * Get the message envelope configuration.
     *
     * Configures the email sender, reply-to, and subject line
     * with order-specific information using OrderHelper.
     *
     * @return Envelope Email envelope configuration
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            replyTo: [new Address(config('mail.from.address'), config('mail.from.name'))],
            subject: 'Order Confirmed - #' . OrderHelper::getOrderNumber($this->order),
        );
    }

    /**
     * Get the message content definition.
     *
     * Defines the email view template and passes order data
     * with OrderHelper formatting utilities.
     *
     * @return Content Email content configuration
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.order.order-confirmed',
            with: [
                'order' => $this->order,
                'orderNumber' => OrderHelper::getOrderNumber($this->order),
                'orderTitle' => OrderHelper::getOrderTitle($this->order),
                'formattedTotal' => OrderHelper::formatAmount($this->order->total_amount),
                'statusLabel' => OrderHelper::getStatusLabel($this->order),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array Array of attachments (currently empty)
     */
    public function attachments(): array
    {
        return [];
    }
}
