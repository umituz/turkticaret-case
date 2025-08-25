<?php

namespace App\Notifications\Order;

use App\Mail\Order\OrderStatusUpdateMail;
use App\Models\Order\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Notification;

/**
 * Order Status Updated Notification for notifying users when their order status changes.
 *
 * Sends email notifications to customers when their order status is updated by admin.
 * Uses Laravel's notification system for better organization and multi-channel support.
 *
 * @package App\Notifications\Order
 */
class OrderStatusUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order,
        public string $oldStatus,
        public string $newStatus
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): Mailable
    {
        return (new OrderStatusUpdateMail($this->order, $this->oldStatus, $this->newStatus))->to($notifiable->email);
    }
}
