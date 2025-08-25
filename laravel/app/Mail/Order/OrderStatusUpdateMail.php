<?php

namespace App\Mail\Order;

use App\Enums\Order\OrderStatusEnum;
use App\Models\Order\Order;
use App\Services\Order\OrderMailService;
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
        public OrderStatusEnum $oldStatus,
        public OrderStatusEnum $newStatus,
        protected OrderMailService $orderMailService
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $statusMessage = $this->newStatus->getLabel();

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
        $emailData = $this->orderMailService->prepareOrderStatusUpdateData(
            $this->order, 
            $this->oldStatus, 
            $this->newStatus
        );
        
        return new Content(
            view: 'emails.order.order-status-update',
            with: array_merge([
                'order' => $this->order,
                'statusDescription' => $this->getStatusDescription($this->newStatus),
            ], $emailData),
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }

    private function getStatusDescription(OrderStatusEnum $status): string
    {
        return match ($status) {
            OrderStatusEnum::CONFIRMED => 'Your order has been confirmed and is being prepared.',
            OrderStatusEnum::PROCESSING => 'Your order is currently being processed and prepared for shipment.',
            OrderStatusEnum::SHIPPED => 'Your order has been shipped and is on its way to you.',
            OrderStatusEnum::DELIVERED => 'Your order has been successfully delivered.',
            OrderStatusEnum::CANCELLED => 'Your order has been cancelled. If you have any questions, please contact our support team.',
            OrderStatusEnum::REFUNDED => 'Your order has been refunded. The refund will be processed to your original payment method.',
            default => 'Your order status has been updated.',
        };
    }
}
