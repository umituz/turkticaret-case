<?php

namespace Database\Seeders\Currency;

use App\Enums\Currency\CurrencyEnum;
use App\Models\Currency\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            CurrencyEnum::TRY,
            CurrencyEnum::USD,
        ];

        foreach ($currencies as $currencyEnum) {
            Currency::updateOrCreate(
                ['code' => $currencyEnum->value],
                [
                    'code' => $currencyEnum->value,
                    'name' => $currencyEnum->getDisplayName(),
                    'symbol' => $currencyEnum->getSymbol(),
                    'decimals' => $currencyEnum->getDecimals(),
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('Currencies seeded successfully.');
    }
}