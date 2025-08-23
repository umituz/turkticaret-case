<?php

namespace Database\Factories\Country;

use App\Enums\Country\CountryEnum;
use App\Models\Country\Country;
use App\Models\Currency\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

class CountryFactory extends Factory
{
    protected $model = Country::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->lexify('??')),
            'name' => $this->faker->unique()->country(),
            'locale' => $this->faker->locale(),
            'currency_uuid' => Currency::factory(),
        ];
    }

    public function withCurrency(Currency $currency): static
    {
        return $this->state(fn (array $attributes) => [
            'currency_uuid' => $currency->uuid,
        ]);
    }
}
