<?php

namespace Database\Seeders\Product;

use App\Models\Category\Category;
use App\Models\Product\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $this->createProducts();
    }

    private function createProducts(): void
    {
        $productData = [
            'electronics' => [
                [
                    'name' => 'iPhone 15 Pro Max',
                    'description' => 'The latest iPhone with cutting-edge technology, powerful A17 Pro chip, and titanium design.',
                    'sku' => 'IPH15PM-256-TIT',
                    'price' => 99900,
                    'stock_quantity' => 25,
                    'is_active' => true,
                    'image_url' => 'https://picsum.photos/800/600?random=iphone15promax',
                ],
                [
                    'name' => 'MacBook Pro 16" M3',
                    'description' => 'Professional laptop with M3 chip, perfect for developers and creative professionals.',
                    'sku' => 'MBP16-M3-512-SLV',
                    'price' => 89900,
                    'stock_quantity' => 12,
                    'is_active' => true,
                    'image_url' => 'https://picsum.photos/800/600?random=macbookprom3',
                ],
                [
                    'name' => 'AirPods Pro 2',
                    'description' => 'Wireless earbuds with active noise cancellation and spatial audio.',
                    'sku' => 'APP-2-001',
                    'price' => 19900,
                    'stock_quantity' => 100,
                    'is_active' => true,
                    'image_url' => 'https://picsum.photos/800/600?random=airpodspro2',
                ],
            ],
            'clothing' => [
                [
                    'name' => 'Nike Air Max 270',
                    'description' => 'Comfortable running shoes with Air Max technology for all-day comfort.',
                    'sku' => 'NIKE-AM270-US10-BLK',
                    'price' => 9900,
                    'stock_quantity' => 45,
                    'is_active' => true,
                    'image_url' => 'https://picsum.photos/800/600?random=nikeairmax270',
                ],
            ],
            'books' => [
                [
                    'name' => 'JavaScript: The Good Parts',
                    'description' => 'Essential JavaScript programming best practices and techniques.',
                    'sku' => 'BOOK-JS-GOODPARTS',
                    'price' => 2900,
                    'stock_quantity' => 50,
                    'is_active' => true,
                    'image_url' => 'https://picsum.photos/800/600?random=jsbook',
                ],
            ],
        ];

        $categories = Category::all()->keyBy('slug');

        foreach ($productData as $categorySlug => $products) {
            $category = $categories->get($categorySlug);

            if (!$category) {
                continue;
            }

            foreach ($products as $productInfo) {
                $this->createProduct($productInfo, $category);
            }
        }

    }

    private function createProduct(array $productData, Category $category): void
    {
        $imageUrl = $productData['image_url'];
        unset($productData['image_url']);

        $product = Product::updateOrCreate(
            ['sku' => $productData['sku']],
            array_merge($productData, ['category_uuid' => $category->uuid])
        );

        #$product->addMediaFromUrl($imageUrl)->toMediaCollection('images');
    }

}
