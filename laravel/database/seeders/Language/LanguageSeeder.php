<?php

namespace Database\Seeders\Language;

use App\Enums\Language\LanguageEnum;
use App\Models\Language\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        $languages = [
            LanguageEnum::TURKISH,
            LanguageEnum::ENGLISH,
        ];

        foreach ($languages as $languageEnum) {
            Language::updateOrCreate(
                ['code' => $languageEnum->value],
                [
                    'code' => $languageEnum->value,
                    'name' => $languageEnum->getDisplayName(),
                    'native_name' => $languageEnum->getNativeName(),
                    'locale' => $languageEnum->getLocale(),
                    'direction' => $languageEnum->getDirection(),
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('Languages seeded successfully.');
    }
}
