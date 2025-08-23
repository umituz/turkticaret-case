<?php

namespace Database\Factories\Setting;

use App\Models\Setting\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Setting>
 */
class SettingFactory extends Factory
{
    protected $model = Setting::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['string', 'integer', 'boolean']);
        $value = match($type) {
            'string' => ['value' => fake()->sentence()],
            'integer' => ['value' => fake()->numberBetween(1, 100)],
            'boolean' => ['value' => fake()->boolean()],
            default => ['value' => fake()->sentence()]
        };

        return [
            'key' => fake()->unique()->word(),
            'value' => $value,
            'type' => $type,
            'group' => fake()->randomElement(['system', 'commerce', 'ui', 'notification']),
            'description' => fake()->sentence(),
            'is_active' => fake()->boolean(),
            'is_editable' => fake()->boolean(),
        ];
    }
}
