<?php

namespace Tests\Unit\Http\Resources\Language;

use App\Http\Resources\Language\LanguageResource;
use Tests\Base\BaseResourceUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Unit tests for LanguageResource
 * Tests language response formatting and structure
 */
#[CoversClass(LanguageResource::class)]
#[Group('unit')]
#[Group('resources')]
#[Small]
class LanguageResourceTest extends BaseResourceUnitTest
{
    protected function getResourceClass(): string
    {
        return LanguageResource::class;
    }

    protected function getResourceData(): array
    {
        return [
            'uuid' => $this->generateTestUuid(),
            'code' => 'en',
            'name' => 'English',
            'native_name' => 'English',
            'locale' => 'en_US',
            'direction' => 'ltr',
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
        $languageData = $this->getResourceData();
        $language = $this->createMockModel($languageData);
        $language->shouldReceive('isRTL')->andReturn(false);
        $request = new Request();

        // Act
        $resource = new LanguageResource($language);
        $result = $resource->toArray($request);

        // Assert
        $this->assertResourceArrayStructure([
            'uuid',
            'code',
            'name',
            'native_name',
            'locale',
            'direction',
            'is_rtl',
            'is_active',
            'created_at',
            'updated_at',
        ], $result);

        $this->assertEquals($languageData['uuid'], $result['uuid']);
        $this->assertEquals($languageData['code'], $result['code']);
        $this->assertEquals($languageData['name'], $result['name']);
        $this->assertEquals($languageData['native_name'], $result['native_name']);
        $this->assertEquals($languageData['locale'], $result['locale']);
        $this->assertEquals($languageData['direction'], $result['direction']);
    }

    #[Test]
    public function toArray_includes_all_language_fields(): void
    {
        // Arrange
        $languageData = [
            'uuid' => 'language-test-uuid',
            'code' => 'tr',
            'name' => 'Turkish',
            'native_name' => 'Türkçe',
            'locale' => 'tr_TR',
            'direction' => 'ltr',
            'is_active' => true,
            'created_at' => Carbon::parse('2024-01-01 10:00:00'),
            'updated_at' => Carbon::parse('2024-01-15 14:30:00'),
        ];
        $language = $this->createMockModel($languageData);
        $language->shouldReceive('isRTL')->andReturn(false);
        $request = new Request();

        // Act
        $resource = new LanguageResource($language);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('language-test-uuid', $result['uuid']);
        $this->assertEquals('tr', $result['code']);
        $this->assertEquals('Turkish', $result['name']);
        $this->assertEquals('Türkçe', $result['native_name']);
        $this->assertEquals('tr_TR', $result['locale']);
        $this->assertEquals('ltr', $result['direction']);
        $this->assertFalse($result['is_rtl']);
        $this->assertTrue($result['is_active']);
    }

    #[Test]
    public function toArray_formats_timestamps_as_iso_string(): void
    {
        // Arrange
        $createdAt = Carbon::parse('2024-01-01 12:00:00');
        $updatedAt = Carbon::parse('2024-01-15 15:30:00');
        $languageData = array_merge($this->getResourceData(), [
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ]);
        $language = $this->createMockModel($languageData);
        $language->shouldReceive('isRTL')->andReturn(false);
        $request = new Request();

        // Act
        $resource = new LanguageResource($language);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals($createdAt->toIso8601String(), $result['created_at']);
        $this->assertEquals($updatedAt->toIso8601String(), $result['updated_at']);
    }

    #[Test]
    public function toArray_handles_null_timestamps(): void
    {
        // Arrange
        $languageData = array_merge($this->getResourceData(), [
            'created_at' => null,
            'updated_at' => null,
        ]);
        $language = $this->createMockModel($languageData);
        $language->shouldReceive('isRTL')->andReturn(false);
        $request = new Request();

        // Act
        $resource = new LanguageResource($language);
        $result = $resource->toArray($request);

        // Assert
        $this->assertNull($result['created_at']);
        $this->assertNull($result['updated_at']);
    }


    #[Test]
    public function toArray_handles_ltr_languages(): void
    {
        // Arrange
        $languageData = array_merge($this->getResourceData(), [
            'code' => 'de',
            'name' => 'German',
            'native_name' => 'Deutsch',
            'locale' => 'de_DE',
            'direction' => 'ltr',
        ]);
        $language = $this->createMockModel($languageData);
        $language->shouldReceive('isRTL')->andReturn(false);
        $request = new Request();

        // Act
        $resource = new LanguageResource($language);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('de', $result['code']);
        $this->assertEquals('German', $result['name']);
        $this->assertEquals('Deutsch', $result['native_name']);
        $this->assertEquals('ltr', $result['direction']);
        $this->assertFalse($result['is_rtl']);
    }


    #[Test]
    public function toArray_handles_complex_native_names(): void
    {
        // Arrange
        $languageData = array_merge($this->getResourceData(), [
            'code' => 'zh',
            'name' => 'Chinese (Simplified)',
            'native_name' => '简体中文',
            'locale' => 'zh_CN',
            'direction' => 'ltr',
        ]);
        $language = $this->createMockModel($languageData);
        $language->shouldReceive('isRTL')->andReturn(false);
        $request = new Request();

        // Act
        $resource = new LanguageResource($language);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('Chinese (Simplified)', $result['name']);
        $this->assertEquals('简体中文', $result['native_name']);
        $this->assertEquals('zh_CN', $result['locale']);
    }

    #[Test]
    public function toArray_validates_uuid_preservation(): void
    {
        // Arrange
        $testUuid = 'f47ac10b-58cc-4372-a567-0e02b2c3d479';
        $languageData = array_merge($this->getResourceData(), [
            'uuid' => $testUuid,
        ]);
        $language = $this->createMockModel($languageData);
        $language->shouldReceive('isRTL')->andReturn(false);
        $request = new Request();

        // Act
        $resource = new LanguageResource($language);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals($testUuid, $result['uuid']);
        $this->assertIsString($result['uuid']);
    }

    #[Test]
    public function toArray_handles_inactive_languages(): void
    {
        // Arrange
        $languageData = array_merge($this->getResourceData(), [
            'is_active' => false,
        ]);
        $language = $this->createMockModel($languageData);
        $language->shouldReceive('isRTL')->andReturn(false);
        $request = new Request();

        // Act
        $resource = new LanguageResource($language);
        $result = $resource->toArray($request);

        // Assert
        $this->assertFalse($result['is_active']);
        $this->assertIsBool($result['is_active']);
    }

    #[Test]
    public function toArray_handles_null_native_name(): void
    {
        // Arrange
        $languageData = array_merge($this->getResourceData(), [
            'native_name' => null,
        ]);
        $language = $this->createMockModel($languageData);
        $language->shouldReceive('isRTL')->andReturn(false);
        $request = new Request();

        // Act
        $resource = new LanguageResource($language);
        $result = $resource->toArray($request);

        // Assert
        $this->assertArrayHasKey('native_name', $result);
        $this->assertNull($result['native_name']);
    }

    #[Test]
    public function toArray_handles_special_locale_formats(): void
    {
        // Arrange
        $languageData = array_merge($this->getResourceData(), [
            'code' => 'pt',
            'name' => 'Portuguese (Brazil)',
            'native_name' => 'Português (Brasil)',
            'locale' => 'pt_BR',
            'direction' => 'ltr',
        ]);
        $language = $this->createMockModel($languageData);
        $language->shouldReceive('isRTL')->andReturn(false);
        $request = new Request();

        // Act
        $resource = new LanguageResource($language);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('pt', $result['code']);
        $this->assertEquals('Portuguese (Brazil)', $result['name']);
        $this->assertEquals('Português (Brasil)', $result['native_name']);
        $this->assertEquals('pt_BR', $result['locale']);
    }


    #[Test]
    public function toArray_handles_unicode_characters_in_native_names(): void
    {
        // Arrange
        $languageData = array_merge($this->getResourceData(), [
            'code' => 'ru',
            'name' => 'Russian',
            'native_name' => 'Русский',
            'locale' => 'ru_RU',
            'direction' => 'ltr',
        ]);
        $language = $this->createMockModel($languageData);
        $language->shouldReceive('isRTL')->andReturn(false);
        $request = new Request();

        // Act
        $resource = new LanguageResource($language);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('Russian', $result['name']);
        $this->assertEquals('Русский', $result['native_name']);
        $this->assertEquals('ru_RU', $result['locale']);
    }
}