<?php

namespace Database\Seeders\Order;

use App\Enums\Order\OrderStatusEnum;
use App\Models\Order\Order;
use App\Models\Order\OrderStatusHistory;
use App\Models\User\User;
use Illuminate\Database\Seeder;

class OrderStatusHistorySeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@test.com')->first();
        
        if (!$admin) {
            return;
        }

        Order::chunk(50, function ($orders) use ($admin) {
            foreach ($orders as $order) {
                OrderStatusHistory::create([
                    'order_uuid' => $order->uuid,
                    'old_status' => null,
                    'new_status' => $order->status->value,
                    'changed_by_uuid' => $admin->uuid,
                    'notes' => 'Initial order status',
                ]);
            }
        });
    }
}
