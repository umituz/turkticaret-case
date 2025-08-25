<?php

namespace App\Notifications\Order;

use App\Mail\Order\OrderConfirmedMail;
use App\Models\Order\Order;
use App\Services\Order\OrderMailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Notification;

/**
 * Order Confirmed Notification for notifying users when their order is confirmed.
 *
 * Sends email notifications to customers when their order is successfully created and confirmed.
 * Uses Laravel's notification system for better organization and multi-channel support.
 *
 * @package App\Notifications\Order
 */
class OrderConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order) {}

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
        $orderMailService = app(OrderMailService::class);

        return (new OrderConfirmedMail($this->order, $orderMailService))->to($notifiable->email);
    }
}
