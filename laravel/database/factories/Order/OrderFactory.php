<?php

namespace Database\Factories\Order;

use App\Models\Auth\User;
use App\Models\Order\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_number' => 'ORD-' . $this->faker->unique()->numerify('########'),
            'user_uuid' => User::factory(),
            'status' => $this->faker->randomElement(['pending', 'processing', 'shipped', 'delivered', 'cancelled']),
            'total_amount' => $this->faker->numberBetween(5000, 100000),
            'shipping_address' => $this->faker->address,
            'notes' => $this->faker->optional()->sentence(),
            'shipped_at' => null,
            'delivered_at' => null,
        ];
    }
}
