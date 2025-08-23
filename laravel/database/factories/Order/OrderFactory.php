<?php

namespace Database\Factories\Order;

use App\Enums\Order\OrderStatusEnum;
use App\Enums\User\UserTypeEnum;
use App\Models\Order\Order;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        $regularUser = User::where('email', UserTypeEnum::USER->getEmail())->first();
        $createdAt = $this->faker->dateTimeBetween('-7 days', 'now');

        $statusWeights = [
            OrderStatusEnum::DELIVERED->value => 60,
            OrderStatusEnum::SHIPPED->value => 20,
            OrderStatusEnum::PROCESSING->value => 10,
            OrderStatusEnum::PENDING->value => 10
        ];

        $status = $this->faker->randomElement(
            array_merge(
                array_fill(0, $statusWeights[OrderStatusEnum::DELIVERED->value], OrderStatusEnum::DELIVERED->value),
                array_fill(0, $statusWeights[OrderStatusEnum::SHIPPED->value], OrderStatusEnum::SHIPPED->value),
                array_fill(0, $statusWeights[OrderStatusEnum::PROCESSING->value], OrderStatusEnum::PROCESSING->value),
                array_fill(0, $statusWeights[OrderStatusEnum::PENDING->value], OrderStatusEnum::PENDING->value)
            )
        );

        $shippedAt = null;
        $deliveredAt = null;

        if (in_array($status, [OrderStatusEnum::SHIPPED->value, OrderStatusEnum::DELIVERED->value])) {
            $shippedAt = $this->faker->dateTimeBetween($createdAt, 'now');
        }

        if ($status === OrderStatusEnum::DELIVERED->value) {
            $deliveredAt = $this->faker->dateTimeBetween($shippedAt ?? $createdAt, 'now');
        }

        return [
            'order_number' => 'ORD-' . $this->faker->unique()->numerify('########'),
            'user_uuid' => $regularUser ? $regularUser->uuid : User::factory(),
            'status' => $status,
            'total_amount' => $this->faker->numberBetween(25000, 500000),
            'shipping_address' => $this->faker->address,
            'notes' => $this->faker->optional()->sentence(),
            'shipped_at' => $shippedAt,
            'delivered_at' => $deliveredAt,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];
    }
}
