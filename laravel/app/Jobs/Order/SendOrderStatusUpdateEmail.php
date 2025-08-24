<?php

namespace App\Jobs\Order;

use App\Mail\Order\OrderStatusUpdateMail;
use App\Models\Order\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

/**
 * Job for sending order status update email notifications.
 * 
 * Queued job that sends email notifications to customers when their
 * order status changes. Includes both old and new status information
 * for comprehensive order tracking communication.
 *
 * @package App\Jobs\Order
 */
class SendOrderStatusUpdateEmail implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order,
        public string $oldStatus,
        public string $newStatus
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->order->user && $this->order->user->email) {
            Mail::to($this->order->user->email)
                ->send(new OrderStatusUpdateMail(
                    $this->order,
                    $this->oldStatus,
                    $this->newStatus
                ));
        }
    }
}
