<?php

namespace Database\Seeders\Order;

use App\Models\Order\Order;
use App\Models\Order\OrderItem;
use App\Models\Product\Product;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Order::factory(20)->create();

        Order::all()->each(function ($order) {
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
        });
    }
}
