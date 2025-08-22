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
        $country = $this->faker->randomElement(CountryEnum::cases());

        return [
            'code' => $country->value,
            'name' => $country->getDisplayName(),
            'locale' => $country->getLocale(),
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
