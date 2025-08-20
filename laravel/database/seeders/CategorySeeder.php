<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Electronics',
                'description' => 'Electronic devices and gadgets',
                'slug' => 'electronics',
                'is_active' => true,
            ],
            [
                'name' => 'Clothing',
                'description' => 'Fashion and apparel items',
                'slug' => 'clothing',
                'is_active' => true,
            ],
            [
                'name' => 'Home & Garden',
                'description' => 'Home decor and garden supplies',
                'slug' => 'home-garden',
                'is_active' => true,
            ],
            [
                'name' => 'Books',
                'description' => 'Books and educational materials',
                'slug' => 'books',
                'is_active' => true,
            ],
            [
                'name' => 'Sports',
                'description' => 'Sports equipment and accessories',
                'slug' => 'sports',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
