<?php

namespace App\Jobs\Order;

use App\Mail\Order\OrderConfirmedMail;
use App\Models\Order\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

/**
 * Job for sending order confirmed emails asynchronously.
 * 
 * This queued job handles sending order confirmed emails to customers
 * after an order is successfully confirmed. It loads necessary relationships
 * and ensures the user has a valid email before sending.
 *
 * @package App\Jobs\Order
 */
class SendOrderConfirmedJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     *
     * @param Order $order The confirmed order to send email for
     */
    public function __construct(public Order $order,) {}

    /**
     * Execute the job to send order confirmed email.
     * 
     * Loads order relationships and sends confirmed email
     * if the user has a valid email address.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->order->load(['user', 'orderItems.product']);

        if ($this->order->user && $this->order->user->email) {
            Mail::to($this->order->user->email)->send(new OrderConfirmedMail($this->order));
        }
    }
}
