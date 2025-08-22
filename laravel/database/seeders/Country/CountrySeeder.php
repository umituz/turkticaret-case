<?php

namespace Database\Seeders\Country;

use App\Enums\Country\CountryEnum;
use App\Models\Country\Country;
use App\Models\Currency\Currency;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            CountryEnum::TURKEY,
            CountryEnum::UNITED_STATES,
        ];

        foreach ($countries as $countryEnum) {
            $currency = Currency::where('code', $countryEnum->getCurrencyCode())->first();
            
            Country::updateOrCreate(
                ['code' => $countryEnum->value],
                [
                    'code' => $countryEnum->value,
                    'name' => $countryEnum->getDisplayName(),
                    'currency_uuid' => $currency?->uuid
                ]
            );
        }

        $this->command->info('Countries seeded successfully.');
    }
}