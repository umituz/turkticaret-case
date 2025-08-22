<?php

namespace Database\Factories\Product;

use App\Models\Category\Category;
use App\Models\Product\Product;
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
            'name' => 'Test Product ' . rand(1000, 9999),
            'description' => 'Test product description ' . rand(1000, 9999),
            'sku' => 'SKU' . rand(100000, 999999),
            'price' => rand(1000, 50000),
            'stock_quantity' => rand(0, 100),
            'is_active' => true,
            'category_uuid' => Category::factory(),
        ];
    }
}
