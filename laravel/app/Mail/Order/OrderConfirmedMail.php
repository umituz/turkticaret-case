<?php

namespace App\Mail\Order;

use App\Models\Order\Order;
use App\Services\Order\OrderMailService;
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
     * @param OrderMailService $orderMailService Service for preparing email data
     */
    public function __construct(
        public Order $order,
        protected OrderMailService $orderMailService
    ) {}

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
        $emailData = $this->orderMailService->prepareOrderConfirmationData($this->order);
        
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            replyTo: [new Address(config('mail.from.address'), config('mail.from.name'))],
            subject: 'Order Confirmed - #' . $emailData['order_number'],
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
        $emailData = $this->orderMailService->prepareOrderConfirmationData($this->order);
        
        return new Content(
            view: 'emails.order.order-confirmed',
            with: array_merge(['order' => $this->order], $emailData),
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