<?php

namespace Database\Seeders\Order;

use App\Enums\Order\OrderStatusEnum;
use App\Models\Order\Order;
use App\Models\Order\OrderItem;
use App\Models\Order\OrderStatusHistory;
use App\Models\Product\Product;
use App\Models\User\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Order::factory(5)->create();

        $adminUser = User::where('email', 'admin@test.com')->first();

        Order::all()->each(function ($order) use ($adminUser) {
            $itemCount = rand(1, 5);
            $totalAmount = 0;

            for ($i = 0; $i < $itemCount; $i++) {
                $product = Product::inRandomOrder()->first();
                $quantity = rand(1, 3);
                $unitPrice = $product->price;
                $totalPrice = $quantity * $unitPrice;
                $totalAmount += $totalPrice;

                OrderItem::create([
                    'order_uuid' => $order->uuid,
                    'product_uuid' => $product->uuid,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                ]);
            }

            $order->update(['total_amount' => $totalAmount]);

            // Create status history for order progression
            $this->createStatusHistory($order, $adminUser);
        });
    }

    private function createStatusHistory(Order $order, User $adminUser): void
    {
        $status = $order->status->value;
        $createdAt = $order->created_at;

        // Always start with pending
        OrderStatusHistory::create([
            'order_uuid' => $order->uuid,
            'old_status' => null,
            'new_status' => OrderStatusEnum::PENDING,
            'changed_by_uuid' => $adminUser->uuid,
            'notes' => 'Order placed by customer',
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        if ($status === OrderStatusEnum::PENDING->value) return;

        // Add confirmed status
        $confirmedAt = $createdAt->copy()->addMinutes(rand(10, 60));
        OrderStatusHistory::create([
            'order_uuid' => $order->uuid,
            'old_status' => OrderStatusEnum::PENDING,
            'new_status' => OrderStatusEnum::CONFIRMED,
            'changed_by_uuid' => $adminUser->uuid,
            'notes' => 'Order confirmed by admin',
            'created_at' => $confirmedAt,
            'updated_at' => $confirmedAt,
        ]);

        if ($status === OrderStatusEnum::CONFIRMED->value) return;

        // Add processing status
        $processingAt = $confirmedAt->copy()->addMinutes(rand(30, 120));
        OrderStatusHistory::create([
            'order_uuid' => $order->uuid,
            'old_status' => OrderStatusEnum::CONFIRMED,
            'new_status' => OrderStatusEnum::PROCESSING,
            'changed_by_uuid' => $adminUser->uuid,
            'notes' => 'Order is being prepared',
            'created_at' => $processingAt,
            'updated_at' => $processingAt,
        ]);

        if ($status === OrderStatusEnum::PROCESSING->value) return;

        // Add shipped status
        $shippedAt = $processingAt->copy()->addMinutes(rand(60, 240));
        OrderStatusHistory::create([
            'order_uuid' => $order->uuid,
            'old_status' => OrderStatusEnum::PROCESSING,
            'new_status' => OrderStatusEnum::SHIPPED,
            'changed_by_uuid' => $adminUser->uuid,
            'notes' => 'Order shipped to customer',
            'created_at' => $shippedAt,
            'updated_at' => $shippedAt,
        ]);

        if ($status === OrderStatusEnum::SHIPPED->value) return;

        // Add delivered status
        $deliveredAt = $shippedAt->copy()->addMinutes(rand(120, 480));
        OrderStatusHistory::create([
            'order_uuid' => $order->uuid,
            'old_status' => OrderStatusEnum::SHIPPED,
            'new_status' => OrderStatusEnum::DELIVERED,
            'changed_by_uuid' => $adminUser->uuid,
            'notes' => 'Order delivered successfully',
            'created_at' => $deliveredAt,
            'updated_at' => $deliveredAt,
        ]);
    }
}
