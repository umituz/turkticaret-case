<?php

namespace App\Jobs\Order;

use App\Mail\Order\OrderConfirmationMail;
use App\Models\Order\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendOrderConfirmationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order,) {}

    public function handle(): void
    {
        $this->order->load(['user', 'orderItems.product']);

        if ($this->order->user && $this->order->user->email) {
            Mail::to($this->order->user->email)->send(new OrderConfirmationMail($this->order));
        }
    }
}
