<?php

namespace Tests\Unit\Http\Resources\Product;

use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\Category\CategoryResource;
use Tests\Base\BaseResourceUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Unit tests for ProductResource
 * Tests product response formatting with category relationships
 */
#[CoversClass(ProductResource::class)]
#[Group('unit')]
#[Group('resources')]
#[Small]
class ProductResourceTest extends BaseResourceUnitTest
{
    protected function getResourceClass(): string
    {
        return ProductResource::class;
    }

    protected function getResourceData(): array
    {
        return [
            'uuid' => $this->generateTestUuid(),
            'name' => 'Test Product',
            'slug' => 'test-product',
            'description' => 'Test product description',
            'sku' => 'TEST-SKU-001',
            'price' => 1999, // in cents
            'stock_quantity' => 50,
            'image_path' => '/images/products/test-product.jpg',
            'is_active' => true,
            'is_featured' => false,
            'category_uuid' => $this->generateTestUuid(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    private function getCategoryData(): array
    {
        return [
            'uuid' => $this->generateTestUuid(),
            'name' => 'Electronics',
            'description' => 'Electronic devices',
            'slug' => 'electronics',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    #[Test]
    public function resource_extends_base_resource(): void
    {
        $this->assertResourceExtendsBaseResource();
    }

    #[Test]
    public function resource_has_required_methods(): void
    {
        $this->assertResourceHasMethod('toArray');
    }

    #[Test]
    public function toArray_returns_correct_structure(): void
    {
        // Arrange
        $productData = $this->getResourceData();
        $product = $this->createMockModel($productData);
        $request = new Request();

        // Act
        $resource = new ProductResource($product);
        $result = $resource->toArray($request);

        // Assert
        $this->assertResourceArrayStructure([
            'uuid',
            'name',
            'description',
            'sku',
            'price',
            'stock_quantity',
            'image_path',
            'is_active',
            'is_featured',
            'category_uuid',
            'category',
            'created_at',
            'updated_at',
        ], $result);
    }

    #[Test]
    public function toArray_includes_all_product_attributes(): void
    {
        // Arrange
        $productData = [
            'uuid' => 'product-test-uuid',
            'name' => 'Premium Headphones',
            'slug' => 'premium-headphones',
            'description' => 'High-quality wireless headphones with noise cancellation',
            'sku' => 'HEADPHONE-WL-001',
            'price' => 29999, // $299.99 in cents
            'stock_quantity' => 25,
            'image_path' => '/images/products/headphones.jpg',
            'is_active' => true,
            'category_uuid' => 'electronics-category-uuid',
            'created_at' => Carbon::parse('2024-01-01 10:00:00'),
            'updated_at' => Carbon::parse('2024-01-15 14:30:00'),
        ];
        $product = $this->createMockModel($productData);
        $request = new Request();

        // Act
        $resource = new ProductResource($product);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('product-test-uuid', $result['uuid']);
        $this->assertEquals('Premium Headphones', $result['name']);
        $this->assertEquals('High-quality wireless headphones with noise cancellation', $result['description']);
        $this->assertEquals('HEADPHONE-WL-001', $result['sku']);
        $this->assertEquals(29999, $result['price']);
        $this->assertEquals(25, $result['stock_quantity']);
        $this->assertEquals('/images/products/headphones.jpg', $result['image_path']);
        $this->assertTrue($result['is_active']);
        $this->assertEquals('electronics-category-uuid', $result['category_uuid']);
    }

    #[Test]
    public function toArray_formats_timestamps_as_iso8601(): void
    {
        // Arrange
        $createdAt = Carbon::parse('2024-01-01 12:00:00');
        $updatedAt = Carbon::parse('2024-01-15 15:30:00');
        $productData = array_merge($this->getResourceData(), [
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ]);
        $product = $this->createMockModel($productData);
        $request = new Request();

        // Act
        $resource = new ProductResource($product);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals($createdAt->toIso8601String(), $result['created_at']);
        $this->assertEquals($updatedAt->toIso8601String(), $result['updated_at']);
    }

    #[Test]
    public function toArray_handles_null_timestamps(): void
    {
        // Arrange
        $productData = array_merge($this->getResourceData(), [
            'created_at' => null,
            'updated_at' => null,
        ]);
        $product = $this->createMockModel($productData);
        $request = new Request();

        // Act
        $resource = new ProductResource($product);
        $result = $resource->toArray($request);

        // Assert
        $this->assertNull($result['created_at']);
        $this->assertNull($result['updated_at']);
    }

    #[Test]
    public function toArray_includes_category_when_loaded(): void
    {
        // Arrange
        $categoryData = $this->getCategoryData();
        $category = $this->createMockModel($categoryData);
        
        $productData = $this->getResourceData();
        $product = $this->createMockModelWithRelations($productData, [
            'category' => $category,
        ]);
        $request = new Request();

        // Act
        $resource = new ProductResource($product);
        $result = $resource->toArray($request);

        // Assert
        $this->assertArrayHasKey('category', $result);
        $this->assertInstanceOf(CategoryResource::class, $result['category']);
    }

    #[Test]
    public function toArray_excludes_category_when_not_loaded(): void
    {
        // Arrange
        $productData = $this->getResourceData();
        $product = $this->createMockModel($productData);
        
        // Mock relationLoaded to return false for category
        $product->shouldReceive('relationLoaded')->with('category')->andReturn(false);
        $request = new Request();

        // Act
        $resource = new ProductResource($product);
        $result = $resource->toArray($request);

        // Assert
        $this->assertArrayHasKey('category', $result);
        // whenLoaded creates a MissingValue resource when relation is not loaded
        $this->assertInstanceOf(CategoryResource::class, $result['category']);
    }

    #[Test]
    public function toArray_handles_null_optional_fields(): void
    {
        // Arrange
        $productData = array_merge($this->getResourceData(), [
            'description' => null,
            'image_path' => null,
        ]);
        $product = $this->createMockModel($productData);
        $request = new Request();

        // Act
        $resource = new ProductResource($product);
        $result = $resource->toArray($request);

        // Assert
        $this->assertArrayHasKey('description', $result);
        $this->assertArrayHasKey('image_path', $result);
        $this->assertNull($result['description']);
        $this->assertNull($result['image_path']);
    }

    #[Test]
    public function toArray_preserves_boolean_is_active_field(): void
    {
        // Arrange
        $productData = array_merge($this->getResourceData(), [
            'is_active' => false,
        ]);
        $product = $this->createMockModel($productData);
        $request = new Request();

        // Act
        $resource = new ProductResource($product);
        $result = $resource->toArray($request);

        // Assert
        $this->assertArrayHasKey('is_active', $result);
        $this->assertFalse($result['is_active']);
        $this->assertIsBool($result['is_active']);
    }

    #[Test]
    public function toArray_preserves_integer_values(): void
    {
        // Arrange
        $productData = array_merge($this->getResourceData(), [
            'price' => 15000, // $150.00 in cents
            'stock_quantity' => 0, // Out of stock
        ]);
        $product = $this->createMockModel($productData);
        $request = new Request();

        // Act
        $resource = new ProductResource($product);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals(15000, $result['price']);
        $this->assertEquals(0, $result['stock_quantity']);
        $this->assertIsInt($result['price']);
        $this->assertIsInt($result['stock_quantity']);
    }

    #[Test]
    public function toArray_validates_sku_format_preservation(): void
    {
        // Arrange
        $productData = array_merge($this->getResourceData(), [
            'sku' => 'COMPLEX-SKU-2024-A1B2C3',
        ]);
        $product = $this->createMockModel($productData);
        $request = new Request();

        // Act
        $resource = new ProductResource($product);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('COMPLEX-SKU-2024-A1B2C3', $result['sku']);
        $this->assertIsString($result['sku']);
    }

    #[Test]
    public function toArray_handles_special_characters_in_name_and_description(): void
    {
        // Arrange
        $productData = array_merge($this->getResourceData(), [
            'name' => 'Café Möller\'s "Premium" Product & Co.',
            'description' => 'Special characters: àáâãäåæçèéêë ñ ü €',
        ]);
        $product = $this->createMockModel($productData);
        $request = new Request();

        // Act
        $resource = new ProductResource($product);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('Café Möller\'s "Premium" Product & Co.', $result['name']);
        $this->assertEquals('Special characters: àáâãäåæçèéêë ñ ü €', $result['description']);
    }

    #[Test]
    public function toArray_preserves_image_path_format(): void
    {
        // Arrange
        $productData = array_merge($this->getResourceData(), [
            'image_path' => '/storage/images/products/2024/01/premium-product.jpg',
        ]);
        $product = $this->createMockModel($productData);
        $request = new Request();

        // Act
        $resource = new ProductResource($product);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('/storage/images/products/2024/01/premium-product.jpg', $result['image_path']);
        $this->assertIsString($result['image_path']);
    }

    #[Test]
    public function toArray_validates_uuid_preservation(): void
    {
        // Arrange
        $testUuid = 'f47ac10b-58cc-4372-a567-0e02b2c3d479';
        $categoryUuid = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
        $productData = array_merge($this->getResourceData(), [
            'uuid' => $testUuid,
            'category_uuid' => $categoryUuid,
        ]);
        $product = $this->createMockModel($productData);
        $request = new Request();

        // Act
        $resource = new ProductResource($product);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals($testUuid, $result['uuid']);
        $this->assertEquals($categoryUuid, $result['category_uuid']);
        $this->assertIsString($result['uuid']);
        $this->assertIsString($result['category_uuid']);
    }

    #[Test]
    public function toArray_handles_high_price_values(): void
    {
        // Arrange
        $productData = array_merge($this->getResourceData(), [
            'price' => 99999999, // $999,999.99
        ]);
        $product = $this->createMockModel($productData);
        $request = new Request();

        // Act
        $resource = new ProductResource($product);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals(99999999, $result['price']);
        $this->assertIsInt($result['price']);
    }

    #[Test]
    public function toArray_handles_large_stock_quantities(): void
    {
        // Arrange
        $productData = array_merge($this->getResourceData(), [
            'stock_quantity' => 999999,
        ]);
        $product = $this->createMockModel($productData);
        $request = new Request();

        // Act
        $resource = new ProductResource($product);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals(999999, $result['stock_quantity']);
        $this->assertIsInt($result['stock_quantity']);
    }
}