<?php

namespace Database\Factories\Category;

use App\Models\Category\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'Test Category ' . rand(1000, 9999);

        return [
            'name' => $name,
            'description' => 'Test category description ' . rand(1000, 9999),
            'slug' => Str::slug($name),
            'is_active' => true,
        ];
    }
}
