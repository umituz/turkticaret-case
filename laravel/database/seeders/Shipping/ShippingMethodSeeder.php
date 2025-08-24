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
                'name' => 'Standard Shipping',
                'description' => 'Standard delivery within business days',
                'price' => 9.99,
                'min_delivery_days' => 3,
                'max_delivery_days' => 5,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Express Shipping',
                'description' => 'Fast delivery for urgent orders',
                'price' => 19.99,
                'min_delivery_days' => 1,
                'max_delivery_days' => 2,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Next Day Delivery',
                'description' => 'Get your order tomorrow',
                'price' => 29.99,
                'min_delivery_days' => 1,
                'max_delivery_days' => 1,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Economy Shipping',
                'description' => 'Budget-friendly shipping option',
                'price' => 4.99,
                'min_delivery_days' => 7,
                'max_delivery_days' => 10,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Free Shipping',
                'description' => 'Free standard shipping on orders over $50',
                'price' => 0.00,
                'min_delivery_days' => 5,
                'max_delivery_days' => 7,
                'is_active' => false,
                'sort_order' => 5,
            ],
        ];

        foreach ($shippingMethods as $method) {
            ShippingMethod::firstOrCreate($method);
        }
    }
}
