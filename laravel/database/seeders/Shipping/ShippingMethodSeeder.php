<?php

namespace Database\Seeders\Shipping;

use App\Models\Shipping\ShippingMethod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShippingMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shippingMethods = [
            [
                'name' => 'Free Shipping',
                'description' => 'Free shipping for all orders',
                'price' => 0.00,
                'min_delivery_days' => 3,
                'max_delivery_days' => 5,
                'is_active' => true,
                'sort_order' => 1,
            ],
        ];

        foreach ($shippingMethods as $method) {
            ShippingMethod::firstOrCreate($method);
        }
    }
}
