<?php

namespace Database\Factories\Currency;

use App\Enums\Currency\CurrencyEnum;
use App\Models\Currency\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

class CurrencyFactory extends Factory
{
    protected $model = Currency::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->lexify('???')),
            'name' => $this->faker->unique()->words(2, true),
            'symbol' => $this->faker->randomElement(['$', '€', '£', '¥', '₺']),
            'decimals' => $this->faker->numberBetween(0, 4),
            'is_active' => true,
        ];
    }
}
