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
        $currency = $this->faker->randomElement(CurrencyEnum::cases());

        return [
            'code' => $currency->value,
            'name' => $currency->getDisplayName(),
            'symbol' => $currency->getSymbol(),
            'decimals' => $currency->getDecimals(),
            'is_active' => true,
        ];
    }
}
