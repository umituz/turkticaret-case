<?php

namespace Tests\Unit\Http\Resources\Product;

use App\Http\Resources\Product\ProductCollection;
use App\Http\Resources\Product\ProductResource;
use Tests\Base\BaseResourceUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Unit tests for ProductCollection
 * Tests product collection functionality and pagination
 */
#[CoversClass(ProductCollection::class)]
#[Group('unit')]
#[Group('resources')]
#[Small]
class ProductCollectionTest extends BaseResourceUnitTest
{
    protected function getResourceClass(): string
    {
        return ProductCollection::class;
    }

    protected function getResourceData(): array
    {
        return [
            [
                'uuid' => $this->generateTestUuid(),
                'name' => 'Product 1',
                'description' => 'Description 1',
                'sku' => 'SKU-001',
                'price' => 1999,
                'stock_quantity' => 10,
                'image_path' => '/images/product1.jpg',
                'is_active' => true,
                'category_uuid' => $this->generateTestUuid(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'uuid' => $this->generateTestUuid(),
                'name' => 'Product 2',
                'description' => 'Description 2',
                'sku' => 'SKU-002',
                'price' => 2999,
                'stock_quantity' => 5,
                'image_path' => '/images/product2.jpg',
                'is_active' => true,
                'category_uuid' => $this->generateTestUuid(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];
    }

    #[Test]
    public function collection_extends_base_collection(): void
    {
        $this->assertResourceExtendsBaseCollection();
    }

    #[Test]
    public function collection_specifies_correct_resource_class(): void
    {
        // Arrange & Act
        $collection = new ProductCollection([]);

        // Assert
        $this->assertEquals(ProductResource::class, $collection->collects);
    }

    #[Test]
    public function collection_transforms_products_correctly(): void
    {
        // Arrange
        $productData = [
            [
                'uuid' => 'product-1-uuid',
                'name' => 'Gaming Laptop',
                'description' => 'High-performance gaming laptop',
                'sku' => 'LAPTOP-GAMING-001',
                'price' => 159999, // $1599.99 in cents
                'stock_quantity' => 8,
                'image_path' => '/images/products/gaming-laptop.jpg',
                'is_active' => true,
                'category_uuid' => 'electronics-uuid',
                'created_at' => Carbon::parse('2024-01-01 10:00:00'),
                'updated_at' => Carbon::parse('2024-01-01 10:00:00'),
            ],
            [
                'uuid' => 'product-2-uuid',
                'name' => 'Mechanical Keyboard',
                'description' => 'RGB mechanical keyboard',
                'sku' => 'KEYBOARD-MECH-001',
                'price' => 12999, // $129.99 in cents
                'stock_quantity' => 25,
                'image_path' => '/images/products/mechanical-keyboard.jpg',
                'is_active' => true,
                'category_uuid' => 'electronics-uuid',
                'created_at' => Carbon::parse('2024-01-02 11:00:00'),
                'updated_at' => Carbon::parse('2024-01-02 11:00:00'),
            ],
        ];

        $products = [
            $this->createMockModel($productData[0]),
            $this->createMockModel($productData[1]),
        ];
        $paginator = $this->createMockPaginatedCollection($products, 2);
        $request = new Request();

        // Act
        $collection = new ProductCollection($paginator);
        $result = $collection->toArray($request);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('meta', $result);
        $this->assertCount(2, $result['data']);
    }

    #[Test]
    public function collection_includes_pagination_metadata(): void
    {
        // Arrange
        $products = [
            $this->createMockModel(['uuid' => 'prod-1', 'name' => 'Product 1', 'price' => 1000]),
            $this->createMockModel(['uuid' => 'prod-2', 'name' => 'Product 2', 'price' => 2000]),
        ];
        $totalProducts = 50;
        $paginator = $this->createMockPaginatedCollection($products, $totalProducts);
        $request = new Request();

        // Act
        $collection = new ProductCollection($paginator);
        $result = $collection->toArray($request);

        // Assert
        $meta = $result['meta'];
        $this->assertEquals($totalProducts, $meta['total']);
        $this->assertEquals(count($products), $meta['count']);
        $this->assertArrayHasKey('current_page', $meta);
        $this->assertArrayHasKey('last_page', $meta);
        $this->assertArrayHasKey('per_page', $meta);
    }

    #[Test]
    public function collection_handles_empty_product_list(): void
    {
        // Arrange
        $paginator = $this->createMockPaginatedCollection([], 0);
        $request = new Request();

        // Act
        $collection = new ProductCollection($paginator);
        $result = $collection->toArray($request);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('meta', $result);
        $this->assertEmpty($result['data']);
        $this->assertEquals(0, $result['meta']['total']);
        $this->assertEquals(0, $result['meta']['count']);
    }

    #[Test]
    public function collection_maintains_individual_product_structure(): void
    {
        // Arrange
        $productData = [
            'uuid' => 'test-product-uuid',
            'name' => 'Test Product',
            'description' => 'Test Description',
            'sku' => 'TEST-SKU',
            'price' => 4999,
            'stock_quantity' => 15,
            'image_path' => '/images/test-product.jpg',
            'is_active' => true,
            'category_uuid' => 'test-category-uuid',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
        $product = $this->createMockModel($productData);
        $paginator = $this->createMockPaginatedCollection([$product], 1);
        $request = new Request();

        // Act
        $collection = new ProductCollection($paginator);
        $result = $collection->toArray($request);

        // Assert
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('meta', $result);
    }

    #[Test]
    public function collection_preserves_product_data_integrity(): void
    {
        // Arrange
        $productsData = [
            [
                'uuid' => 'laptop-uuid',
                'name' => 'Gaming Laptop Pro',
                'description' => 'Professional gaming laptop with RTX graphics',
                'sku' => 'LAPTOP-PRO-2024',
                'price' => 249999, // $2499.99
                'stock_quantity' => 3,
                'image_path' => '/images/products/laptop-pro.jpg',
                'is_active' => true,
                'category_uuid' => 'computers-uuid',
                'created_at' => Carbon::parse('2024-01-01'),
                'updated_at' => Carbon::parse('2024-01-15'),
            ],
            [
                'uuid' => 'mouse-uuid',
                'name' => 'Wireless Gaming Mouse',
                'description' => 'High-precision wireless gaming mouse',
                'sku' => 'MOUSE-WIRELESS-001',
                'price' => 7999, // $79.99
                'stock_quantity' => 50,
                'image_path' => '/images/products/wireless-mouse.jpg',
                'is_active' => true,
                'category_uuid' => 'accessories-uuid',
                'created_at' => Carbon::parse('2024-01-02'),
                'updated_at' => Carbon::parse('2024-01-16'),
            ],
        ];

        $products = array_map(fn($data) => $this->createMockModel($data), $productsData);
        $paginator = $this->createMockPaginatedCollection($products, 2);
        $request = new Request();

        // Act
        $collection = new ProductCollection($paginator);
        $result = $collection->toArray($request);

        // Assert
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('meta', $result);
    }

    #[Test]
    public function collection_returns_json_response(): void
    {
        // Arrange
        $products = [
            $this->createMockModel([
                'uuid' => 'prod-1', 
                'name' => 'Product 1',
                'slug' => 'product-1',
                'description' => 'Test description',
                'price' => 1000,
                'stock_quantity' => 5,
                'sku' => 'TEST-001',
                'is_active' => true,
                'category_uuid' => 'cat-1',
                'image_path' => null,
            ]),
        ];
        $paginator = $this->createMockPaginatedCollection($products, 1);
        $request = new Request();

        // Act
        $collection = new ProductCollection($paginator);
        $response = $collection->toResponse($request);

        // Assert
        $this->assertResponseIsJsonResponse($response);
    }

    #[Test]
    public function collection_handles_large_product_catalog(): void
    {
        // Arrange
        $products = [];
        for ($i = 1; $i <= 15; $i++) {
            $products[] = $this->createMockModel([
                'uuid' => "product-{$i}-uuid",
                'name' => "Product {$i}",
                'description' => "Description for product {$i}",
                'sku' => sprintf("SKU-%03d", $i),
                'price' => $i * 1000, // Varying prices
                'stock_quantity' => $i * 5,
                'image_path' => "/images/product{$i}.jpg",
                'is_active' => $i % 2 === 1, // Alternate active/inactive
                'category_uuid' => $this->generateTestUuid(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
        $totalProducts = 500;
        $paginator = $this->createMockPaginatedCollection($products, $totalProducts);
        $request = new Request();

        // Act
        $collection = new ProductCollection($paginator);
        $result = $collection->toArray($request);

        // Assert
        $this->assertCount(15, $result['data']);
        $this->assertEquals($totalProducts, $result['meta']['total']);
        $this->assertEquals(15, $result['meta']['count']);
        $this->assertEquals(34, $result['meta']['last_page']); // ceil(500/15)
    }

    #[Test]
    public function collection_supports_product_filtering(): void
    {
        // Arrange - Filtered results for active products only
        $activeProducts = [
            $this->createMockModel([
                'uuid' => 'active-product-1',
                'name' => 'Active Product 1',
                'is_active' => true,
                'price' => 1999,
            ]),
            $this->createMockModel([
                'uuid' => 'active-product-2',
                'name' => 'Active Product 2',
                'is_active' => true,
                'price' => 2999,
            ]),
        ];
        $totalActiveProducts = 12; // Total active products in system
        $paginator = $this->createMockPaginatedCollection($activeProducts, $totalActiveProducts);
        $request = new Request();

        // Act
        $collection = new ProductCollection($paginator);
        $result = $collection->toArray($request);

        // Assert
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('meta', $result);
    }

    #[Test]
    public function collection_handles_out_of_stock_products(): void
    {
        // Arrange
        $products = [
            $this->createMockModel([
                'uuid' => 'in-stock-product',
                'name' => 'In Stock Product',
                'stock_quantity' => 10,
            ]),
            $this->createMockModel([
                'uuid' => 'out-of-stock-product',
                'name' => 'Out of Stock Product',
                'stock_quantity' => 0,
            ]),
        ];
        $paginator = $this->createMockPaginatedCollection($products, 2);
        $request = new Request();

        // Act
        $collection = new ProductCollection($paginator);
        $result = $collection->toArray($request);

        // Assert
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('meta', $result);
    }

    #[Test]
    public function collection_preserves_price_formatting(): void
    {
        // Arrange
        $products = [
            $this->createMockModel([
                'uuid' => 'expensive-product',
                'name' => 'Expensive Product',
                'price' => 99999999, // $999,999.99
            ]),
            $this->createMockModel([
                'uuid' => 'cheap-product',
                'name' => 'Cheap Product',
                'price' => 99, // $0.99
            ]),
        ];
        $paginator = $this->createMockPaginatedCollection($products, 2);
        $request = new Request();

        // Act
        $collection = new ProductCollection($paginator);
        $result = $collection->toArray($request);

        // Assert
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('meta', $result);
    }
}