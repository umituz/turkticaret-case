<?php

namespace Database\Factories\Language;

use App\Enums\Language\LanguageEnum;
use App\Models\Language\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

class LanguageFactory extends Factory
{
    protected $model = Language::class;

    public function definition(): array
    {
        $language = $this->faker->randomElement(LanguageEnum::cases());

        return [
            'code' => $language->value,
            'name' => $language->getDisplayName(),
            'native_name' => $language->getNativeName(),
            'locale' => $language->getLocale(),
            'direction' => $language->getDirection(),
            'is_active' => true,
        ];
    }
}
