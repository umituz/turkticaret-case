<?php

namespace Database\Seeders\Product;

use App\Models\Category\Category;
use App\Models\Product\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all();

        $electronicsCategory = $categories->where('slug', 'electronics')->first();
        if ($electronicsCategory) {
            $this->createElectronicsProducts($electronicsCategory);
        }

        $clothingCategory = $categories->where('slug', 'clothing')->first();
        if ($clothingCategory) {
            $this->createClothingProducts($clothingCategory);
        }

        $homeGardenCategory = $categories->where('slug', 'home-garden')->first();
        if ($homeGardenCategory) {
            $this->createHomeGardenProducts($homeGardenCategory);
        }

        // Create products for other categories using factory
        $categories->whereNotIn('slug', ['electronics', 'clothing', 'home-garden'])->each(function ($category) {
            Product::factory(5)->create([
                'category_uuid' => $category->uuid,
            ]);
        });
    }

    private function createElectronicsProducts(Category $category): void
    {
        $products = [
            [
                'name' => 'iPhone 15 Pro',
                'description' => 'Latest iPhone with advanced camera and A17 Pro chip',
                'sku' => 'IPH-15-PRO-001',
                'price' => 129900, // 1299.00 TL in cents
                'stock_quantity' => 50,
                'image_path' => '/images/products/iphone-15-pro.jpg',
                'is_active' => true,
            ],
            [
                'name' => 'Samsung Galaxy S24',
                'description' => 'Flagship Android smartphone with AI features',
                'sku' => 'SAM-S24-001',
                'price' => 119900,
                'stock_quantity' => 30,
                'image_path' => '/images/products/galaxy-s24.jpg',
                'is_active' => true,
            ],
            [
                'name' => 'MacBook Pro 14"',
                'description' => 'Professional laptop with M3 chip and Retina display',
                'sku' => 'MBP-14-M3-001',
                'price' => 349900,
                'stock_quantity' => 15,
                'image_path' => '/images/products/macbook-pro-14.jpg',
                'is_active' => true,
            ],
            [
                'name' => 'AirPods Pro 2',
                'description' => 'Wireless earbuds with active noise cancellation',
                'sku' => 'APP-2-001',
                'price' => 34900,
                'stock_quantity' => 100,
                'image_path' => '/images/products/airpods-pro-2.jpg',
                'is_active' => true,
            ],
            [
                'name' => 'iPad Air 10.9"',
                'description' => 'Powerful tablet for work and creativity',
                'sku' => 'IPAD-AIR-109-001',
                'price' => 79900,
                'stock_quantity' => 25,
                'image_path' => '/images/products/ipad-air.jpg',
                'is_active' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['sku' => $product['sku']],
                array_merge($product, ['category_uuid' => $category->uuid])
            );
        }
    }

    private function createClothingProducts(Category $category): void
    {
        $products = [
            [
                'name' => 'Classic White T-Shirt',
                'description' => 'Comfortable cotton t-shirt for everyday wear',
                'sku' => 'TSH-WHT-001',
                'price' => 2500,
                'stock_quantity' => 200,
                'image_path' => '/images/products/white-tshirt.jpg',
                'is_active' => true,
            ],
            [
                'name' => 'Denim Jeans',
                'description' => 'Classic blue denim jeans with perfect fit',
                'sku' => 'JNS-DEN-001',
                'price' => 8900,
                'stock_quantity' => 75,
                'image_path' => '/images/products/denim-jeans.jpg',
                'is_active' => true,
            ],
            [
                'name' => 'Winter Jacket',
                'description' => 'Warm and stylish winter jacket for cold weather',
                'sku' => 'JKT-WIN-001',
                'price' => 15900,
                'stock_quantity' => 40,
                'image_path' => '/images/products/winter-jacket.jpg',
                'is_active' => true,
            ],
            [
                'name' => 'Running Shoes',
                'description' => 'Comfortable running shoes for sports activities',
                'sku' => 'SHO-RUN-001',
                'price' => 12900,
                'stock_quantity' => 60,
                'image_path' => '/images/products/running-shoes.jpg',
                'is_active' => true,
            ],
            [
                'name' => 'Cotton Hoodie',
                'description' => 'Soft cotton hoodie for casual comfort',
                'sku' => 'HOO-COT-001',
                'price' => 6900,
                'stock_quantity' => 90,
                'image_path' => '/images/products/cotton-hoodie.jpg',
                'is_active' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['sku' => $product['sku']],
                array_merge($product, ['category_uuid' => $category->uuid])
            );
        }
    }

    private function createHomeGardenProducts(Category $category): void
    {
        $products = [
            [
                'name' => 'Coffee Maker Deluxe',
                'description' => 'Programmable coffee maker with thermal carafe',
                'sku' => 'COF-MAK-001',
                'price' => 18900,
                'stock_quantity' => 35,
                'image_path' => '/images/products/coffee-maker.jpg',
                'is_active' => true,
            ],
            [
                'name' => 'Garden Tool Set',
                'description' => 'Complete 5-piece gardening tool set with storage bag',
                'sku' => 'GAR-TOL-001',
                'price' => 7900,
                'stock_quantity' => 80,
                'image_path' => '/images/products/garden-tools.jpg',
                'is_active' => true,
            ],
            [
                'name' => 'Decorative Table Lamp',
                'description' => 'Modern LED table lamp with adjustable brightness',
                'sku' => 'LAM-TBL-001',
                'price' => 4500,
                'stock_quantity' => 120,
                'image_path' => '/images/products/table-lamp.jpg',
                'is_active' => true,
            ],
            [
                'name' => 'Outdoor Plant Pot Set',
                'description' => 'Set of 3 ceramic plant pots for outdoor use',
                'sku' => 'POT-OUT-001',
                'price' => 5900,
                'stock_quantity' => 65,
                'image_path' => '/images/products/plant-pots.jpg',
                'is_active' => true,
            ],
            [
                'name' => 'Kitchen Knife Set',
                'description' => '7-piece professional kitchen knife set with wooden block',
                'sku' => 'KNI-SET-001',
                'price' => 12900,
                'stock_quantity' => 45,
                'image_path' => '/images/products/knife-set.jpg',
                'is_active' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['sku' => $product['sku']],
                array_merge($product, ['category_uuid' => $category->uuid])
            );
        }
    }
}
