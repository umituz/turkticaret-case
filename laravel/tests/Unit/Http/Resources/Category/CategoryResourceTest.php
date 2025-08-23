<?php

namespace Tests\Unit\Http\Resources\Category;

use App\Http\Resources\Category\CategoryResource;
use Tests\Base\BaseResourceUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Unit tests for CategoryResource
 * Tests category response formatting and structure
 */
#[CoversClass(CategoryResource::class)]
#[Group('unit')]
#[Group('resources')]
#[Small]
class CategoryResourceTest extends BaseResourceUnitTest
{
    protected function getResourceClass(): string
    {
        return CategoryResource::class;
    }

    protected function getResourceData(): array
    {
        return [
            'uuid' => $this->generateTestUuid(),
            'name' => 'Electronics',
            'description' => 'Electronic devices and accessories',
            'slug' => 'electronics',
            'is_active' => true,
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
        $categoryData = $this->getResourceData();
        $category = $this->createMockModel($categoryData);
        $request = new Request();

        // Act
        $resource = new CategoryResource($category);
        $result = $resource->toArray($request);

        // Assert
        $this->assertResourceArrayStructure([
            'uuid',
            'name',
            'description',
            'slug',
            'is_active',
            'created_at',
            'updated_at',
        ], $result);

        $this->assertEquals($categoryData['uuid'], $result['uuid']);
        $this->assertEquals($categoryData['name'], $result['name']);
        $this->assertEquals($categoryData['description'], $result['description']);
        $this->assertEquals($categoryData['slug'], $result['slug']);
    }

    #[Test]
    public function toArray_includes_all_category_fields(): void
    {
        // Arrange
        $categoryData = [
            'uuid' => 'category-test-uuid',
            'name' => 'Books & Media',
            'description' => 'Books, magazines, and media content',
            'slug' => 'books-media',
            'is_active' => true,
            'created_at' => Carbon::parse('2024-01-01 10:00:00'),
            'updated_at' => Carbon::parse('2024-01-15 14:30:00'),
        ];
        $category = $this->createMockModel($categoryData);
        $request = new Request();

        // Act
        $resource = new CategoryResource($category);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('category-test-uuid', $result['uuid']);
        $this->assertEquals('Books & Media', $result['name']);
        $this->assertEquals('Books, magazines, and media content', $result['description']);
        $this->assertEquals('books-media', $result['slug']);
    }

    #[Test]
    public function toArray_formats_timestamps_as_iso8601(): void
    {
        // Arrange
        $createdAt = Carbon::parse('2024-01-01 12:00:00');
        $updatedAt = Carbon::parse('2024-01-15 15:30:00');
        $categoryData = array_merge($this->getResourceData(), [
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ]);
        $category = $this->createMockModel($categoryData);
        $request = new Request();

        // Act
        $resource = new CategoryResource($category);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals($createdAt->toIso8601String(), $result['created_at']);
        $this->assertEquals($updatedAt->toIso8601String(), $result['updated_at']);
    }

    #[Test]
    public function toArray_handles_null_timestamps(): void
    {
        // Arrange
        $categoryData = array_merge($this->getResourceData(), [
            'created_at' => null,
            'updated_at' => null,
        ]);
        $category = $this->createMockModel($categoryData);
        $request = new Request();

        // Act
        $resource = new CategoryResource($category);
        $result = $resource->toArray($request);

        // Assert
        $this->assertNull($result['created_at']);
        $this->assertNull($result['updated_at']);
    }

    #[Test]
    public function toArray_handles_null_description(): void
    {
        // Arrange
        $categoryData = array_merge($this->getResourceData(), [
            'description' => null,
        ]);
        $category = $this->createMockModel($categoryData);
        $request = new Request();

        // Act
        $resource = new CategoryResource($category);
        $result = $resource->toArray($request);

        // Assert
        $this->assertArrayHasKey('description', $result);
        $this->assertNull($result['description']);
    }

    #[Test]
    public function toArray_preserves_slug_format(): void
    {
        // Arrange
        $categoryData = array_merge($this->getResourceData(), [
            'name' => 'Home & Garden',
            'slug' => 'home-garden',
            'is_active' => true,
        ]);
        $category = $this->createMockModel($categoryData);
        $request = new Request();

        // Act
        $resource = new CategoryResource($category);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('home-garden', $result['slug']);
        $this->assertIsString($result['slug']);
    }

    #[Test]
    public function toArray_handles_complex_category_names(): void
    {
        // Arrange
        $categoryData = array_merge($this->getResourceData(), [
            'name' => 'Health & Beauty / Personal Care',
            'description' => 'Health, beauty, and personal care products & accessories',
            'slug' => 'health-beauty-personal-care',
            'is_active' => true,
        ]);
        $category = $this->createMockModel($categoryData);
        $request = new Request();

        // Act
        $resource = new CategoryResource($category);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('Health & Beauty / Personal Care', $result['name']);
        $this->assertEquals('Health, beauty, and personal care products & accessories', $result['description']);
        $this->assertEquals('health-beauty-personal-care', $result['slug']);
    }

    #[Test]
    public function toArray_supports_unicode_in_name_and_description(): void
    {
        // Arrange
        $categoryData = array_merge($this->getResourceData(), [
            'name' => 'Spor & Rekreasyon',
            'description' => 'Spor malzemeleri ve rekreasyon ürünleri',
            'slug' => 'spor-rekreasyon',
            'is_active' => true,
        ]);
        $category = $this->createMockModel($categoryData);
        $request = new Request();

        // Act
        $resource = new CategoryResource($category);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('Spor & Rekreasyon', $result['name']);
        $this->assertEquals('Spor malzemeleri ve rekreasyon ürünleri', $result['description']);
    }

    #[Test]
    public function toArray_validates_uuid_preservation(): void
    {
        // Arrange
        $testUuid = 'f47ac10b-58cc-4372-a567-0e02b2c3d479';
        $categoryData = array_merge($this->getResourceData(), [
            'uuid' => $testUuid,
            'is_active' => true,
        ]);
        $category = $this->createMockModel($categoryData);
        $request = new Request();

        // Act
        $resource = new CategoryResource($category);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals($testUuid, $result['uuid']);
        $this->assertIsString($result['uuid']);
    }

    #[Test]
    public function toArray_handles_empty_string_values(): void
    {
        // Arrange
        $categoryData = array_merge($this->getResourceData(), [
            'name' => '',
            'description' => '',
            'slug' => '',
            'is_active' => false,
        ]);
        $category = $this->createMockModel($categoryData);
        $request = new Request();

        // Act
        $resource = new CategoryResource($category);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('', $result['name']);
        $this->assertEquals('', $result['description']);
        $this->assertEquals('', $result['slug']);
    }
}