<?php

namespace Tests\Unit\Http\Resources\Language;

use App\Http\Resources\Language\LanguageCollection;
use App\Http\Resources\Language\LanguageResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Tests\Base\BaseResourceUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;

/**
 * Unit tests for LanguageCollection
 * Tests language collection functionality with original BaseCollection
 */
#[CoversClass(LanguageCollection::class)]
#[Group('unit')]
#[Group('resources')]
#[Small]
class LanguageCollectionTest extends BaseResourceUnitTest
{
    protected function getResourceClass(): string
    {
        return LanguageCollection::class;
    }

    protected function getResourceData(): array
    {
        return [
            [
                'uuid' => $this->generateTestUuid(),
                'name' => 'English',
                'code' => 'en',
                'native_name' => 'English',
                'direction' => 'ltr',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'uuid' => $this->generateTestUuid(),
                'name' => 'Turkish',
                'code' => 'tr',
                'native_name' => 'Türkçe',
                'direction' => 'ltr',
                'is_active' => true,
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
        $collection = new LanguageCollection([]);

        // Assert
        $this->assertEquals(LanguageResource::class, $collection->collects);
    }

    #[Test]
    public function collection_transforms_languages_correctly(): void
    {
        // Arrange
        $languageData = $this->getResourceData();
        $languages = array_map(fn($data) => $this->createMockModel($data), $languageData);
        $paginator = $this->createMockPaginatedCollection($languages, 2);
        $request = new Request();

        // Act
        $collection = new LanguageCollection($paginator);
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
        $languages = [
            $this->createMockModel(['uuid' => 'lang-1', 'name' => 'English', 'is_active' => true]),
            $this->createMockModel(['uuid' => 'lang-2', 'name' => 'Turkish', 'is_active' => true]),
        ];
        $totalLanguages = 25;
        $paginator = $this->createMockPaginatedCollection($languages, $totalLanguages);
        $request = new Request();

        // Act
        $collection = new LanguageCollection($paginator);
        $result = $collection->toArray($request);

        // Assert
        $meta = $result['meta'];
        $this->assertEquals($totalLanguages, $meta['total']);
        $this->assertEquals(count($languages), $meta['count']);
        $this->assertArrayHasKey('current_page', $meta);
        $this->assertArrayHasKey('last_page', $meta);
        $this->assertArrayHasKey('per_page', $meta);
    }

    #[Test]
    public function collection_handles_empty_language_list(): void
    {
        // Arrange
        $paginator = $this->createMockPaginatedCollection([], 0);
        $request = new Request();

        // Act
        $collection = new LanguageCollection($paginator);
        $result = $collection->toArray($request);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('meta', $result);
        $this->assertEmpty($result['data']);
        $this->assertEquals(0, $result['meta']['total']);
        $this->assertEquals(0, $result['meta']['count']);
    }
}