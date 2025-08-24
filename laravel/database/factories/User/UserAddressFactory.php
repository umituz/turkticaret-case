<?php

namespace Database\Factories\User;

use App\Models\Country\Country;
use App\Models\User\User;
use App\Models\User\UserAddress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserAddress>
 */
class UserAddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $addressTypes = ['billing', 'shipping'];

        return [
            'user_uuid' => User::factory(),
            'type' => fake()->randomElement($addressTypes),
            'is_default' => fake()->boolean(20),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'company' => fake()->optional(0.3)->company(),
            'address_line_1' => fake()->streetAddress(),
            'address_line_2' => fake()->optional(0.3)->secondaryAddress(),
            'city' => fake()->city(),
            'state' => fake()->optional()->state(),
            'postal_code' => fake()->postcode(),
            'country_uuid' => Country::factory(),
            'phone' => fake()->optional(0.7)->phoneNumber(),
        ];
    }

    public function billing(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'billing',
        ]);
    }

    public function shipping(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'shipping',
        ]);
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }
}
