<?php

namespace Database\Factories\Shipping;

use App\Models\Shipping\ShippingMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShippingMethod>
 */
class ShippingMethodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $deliveryDays = $this->faker->numberBetween(1, 10);
        $maxDeliveryDays = $this->faker->numberBetween($deliveryDays, $deliveryDays + 5);

        return [
            'name' => $this->faker->randomElement(['Standard Shipping', 'Express Shipping', 'Next Day Delivery', 'Economy Shipping', 'Premium Shipping']),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 0, 50),
            'min_delivery_days' => $deliveryDays,
            'max_delivery_days' => $maxDeliveryDays,
            'is_active' => $this->faker->boolean(80),
            'sort_order' => $this->faker->numberBetween(1, 10),
        ];
    }
}
