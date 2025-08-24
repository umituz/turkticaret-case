<?php

namespace App\Mail\Order;

use App\Models\Order\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Mailable class for order status update notifications.
 * 
 * Sends email notifications to customers when their order status changes.
 * Includes dynamic subject lines, status descriptions, and formatted
 * email content for professional order communication.
 *
 * @package App\Mail\Order
 */
class OrderStatusUpdateMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $oldStatus,
        public string $newStatus
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $statusMessage = $this->getStatusMessage($this->newStatus);

        return new Envelope(
            from: config('mail.from.address'),
            subject: "Order #{$this->order->order_number} - {$statusMessage}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.order-status-update',
            with: [
                'order' => $this->order,
                'oldStatus' => $this->oldStatus,
                'newStatus' => $this->newStatus,
                'statusMessage' => $this->getStatusMessage($this->newStatus),
                'statusDescription' => $this->getStatusDescription($this->newStatus),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }

    private function getStatusMessage(string $status): string
    {
        return match ($status) {
            'confirmed' => 'Order Confirmed',
            'processing' => 'Order Processing',
            'shipped' => 'Order Shipped',
            'delivered' => 'Order Delivered',
            'cancelled' => 'Order Cancelled',
            'refunded' => 'Order Refunded',
            default => 'Status Updated',
        };
    }

    private function getStatusDescription(string $status): string
    {
        return match ($status) {
            'confirmed' => 'Your order has been confirmed and is being prepared.',
            'processing' => 'Your order is currently being processed and prepared for shipment.',
            'shipped' => 'Your order has been shipped and is on its way to you.',
            'delivered' => 'Your order has been successfully delivered.',
            'cancelled' => 'Your order has been cancelled. If you have any questions, please contact our support team.',
            'refunded' => 'Your order has been refunded. The refund will be processed to your original payment method.',
            default => 'Your order status has been updated.',
        };
    }
}
