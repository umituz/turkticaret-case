<?php

namespace Database\Factories;

use App\Models\Category\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'sku' => $this->faker->unique()->regexify('[A-Z]{3}[0-9]{6}'),
            'price' => $this->faker->numberBetween(1000, 50000),
            'stock_quantity' => $this->faker->numberBetween(0, 100),
            'image_path' => null,
            'is_active' => $this->faker->boolean(85),
            'category_uuid' => Category::factory(),
        ];
    }
}
