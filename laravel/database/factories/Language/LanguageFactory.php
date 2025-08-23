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
        return [
            'code' => $this->faker->unique()->lexify('??'),
            'name' => $this->faker->unique()->words(2, true),
            'native_name' => $this->faker->unique()->words(2, true),
            'locale' => $this->faker->locale(),
            'direction' => 'ltr',
            'is_active' => $this->faker->boolean(85),
        ];
    }

    public function english(): static
    {
        return $this->state(function (array $attributes) {
            $english = LanguageEnum::ENGLISH;
            return [
                'code' => $english->value,
                'name' => $english->getDisplayName(),
                'native_name' => $english->getNativeName(),
                'locale' => $english->getLocale(),
                'direction' => $english->getDirection(),
            ];
        });
    }

    public function turkish(): static
    {
        return $this->state(function (array $attributes) {
            $turkish = LanguageEnum::TURKISH;
            return [
                'code' => $turkish->value,
                'name' => $turkish->getDisplayName(),
                'native_name' => $turkish->getNativeName(),
                'locale' => $turkish->getLocale(),
                'direction' => $turkish->getDirection(),
            ];
        });
    }

    public function active(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => true,
            ];
        });
    }

    public function inactive(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }
}
