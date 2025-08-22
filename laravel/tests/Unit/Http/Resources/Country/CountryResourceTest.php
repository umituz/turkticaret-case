<?php

namespace Tests\Unit\Http\Resources\Country;

use App\Http\Resources\Country\CountryResource;
use Tests\Base\BaseResourceUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Unit tests for CountryResource
 * Tests country response formatting and structure
 */
#[CoversClass(CountryResource::class)]
#[Group('unit')]
#[Group('resources')]
#[Small]
class CountryResourceTest extends BaseResourceUnitTest
{
    protected function getResourceClass(): string
    {
        return CountryResource::class;
    }

    protected function getResourceData(): array
    {
        return [
            'uuid' => $this->generateTestUuid(),
            'code' => 'US',
            'name' => 'United States',
            'currency_uuid' => $this->generateTestUuid(),
            'currency_code' => 'USD',
            'currency_symbol' => '$',
            'locale' => 'en_US',
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
        $countryData = $this->getResourceData();
        $country = $this->createMockModel($countryData);
        $request = new Request();

        // Act
        $resource = new CountryResource($country);
        $result = $resource->toArray($request);

        // Assert
        $this->assertResourceArrayStructure([
            'uuid',
            'code',
            'name',
            'currency_code',
            'currency_symbol',
            'locale',
            'is_active',
            'created_at',
            'updated_at',
        ], $result);

        $this->assertEquals($countryData['uuid'], $result['uuid']);
        $this->assertEquals($countryData['code'], $result['code']);
        $this->assertEquals($countryData['name'], $result['name']);
        $this->assertEquals($countryData['currency_code'], $result['currency_code']);
        $this->assertEquals($countryData['currency_symbol'], $result['currency_symbol']);
        $this->assertEquals($countryData['locale'], $result['locale']);
    }

    #[Test]
    public function toArray_includes_all_country_fields(): void
    {
        // Arrange
        $countryData = [
            'uuid' => 'country-test-uuid',
            'code' => 'TR',
            'name' => 'Turkey',
            'currency_uuid' => $this->generateTestUuid(),
            'currency_code' => 'TRY',
            'currency_symbol' => '₺',
            'locale' => 'tr_TR',
            'is_active' => true,
            'created_at' => Carbon::parse('2024-01-01 10:00:00'),
            'updated_at' => Carbon::parse('2024-01-15 14:30:00'),
        ];
        $country = $this->createMockModel($countryData);
        $request = new Request();

        // Act
        $resource = new CountryResource($country);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('country-test-uuid', $result['uuid']);
        $this->assertEquals('TR', $result['code']);
        $this->assertEquals('Turkey', $result['name']);
        $this->assertEquals('TRY', $result['currency_code']);
        $this->assertEquals('₺', $result['currency_symbol']);
        $this->assertEquals('tr_TR', $result['locale']);
    }

    #[Test]
    public function toArray_formats_timestamps_as_iso_string(): void
    {
        // Arrange
        $createdAt = Carbon::parse('2024-01-01 12:00:00');
        $updatedAt = Carbon::parse('2024-01-15 15:30:00');
        $countryData = array_merge($this->getResourceData(), [
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ]);
        $country = $this->createMockModel($countryData);
        $request = new Request();

        // Act
        $resource = new CountryResource($country);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals($createdAt->toIso8601String(), $result['created_at']);
        $this->assertEquals($updatedAt->toIso8601String(), $result['updated_at']);
    }

    #[Test]
    public function toArray_handles_null_timestamps(): void
    {
        // Arrange
        $countryData = array_merge($this->getResourceData(), [
            'created_at' => null,
            'updated_at' => null,
        ]);
        $country = $this->createMockModel($countryData);
        $request = new Request();

        // Act
        $resource = new CountryResource($country);
        $result = $resource->toArray($request);

        // Assert
        $this->assertNull($result['created_at']);
        $this->assertNull($result['updated_at']);
    }

    #[Test]
    public function toArray_handles_null_optional_fields(): void
    {
        // Arrange
        $countryData = array_merge($this->getResourceData(), [
            'currency_uuid' => null,
            'currency_code' => null,
            'currency_symbol' => null,
            'locale' => null,
        ]);
        $country = $this->createMockModel($countryData);
        $request = new Request();

        // Act
        $resource = new CountryResource($country);
        $result = $resource->toArray($request);

        // Assert
        $this->assertArrayHasKey('currency_code', $result);
        $this->assertArrayHasKey('currency_symbol', $result);
        $this->assertArrayHasKey('locale', $result);
        $this->assertNull($result['currency_code']);
        $this->assertNull($result['currency_symbol']);
        $this->assertNull($result['locale']);
    }

    #[Test]
    public function toArray_preserves_country_code_format(): void
    {
        // Arrange
        $countryData = array_merge($this->getResourceData(), [
            'code' => 'GB',
            'name' => 'United Kingdom',
            'currency_uuid' => $this->generateTestUuid(),
            'currency_code' => 'GBP',
            'currency_symbol' => '£',
        ]);
        $country = $this->createMockModel($countryData);
        $request = new Request();

        // Act
        $resource = new CountryResource($country);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('GB', $result['code']);
        $this->assertEquals('GBP', $result['currency_code']);
        $this->assertEquals('£', $result['currency_symbol']);
        $this->assertIsString($result['code']);
    }


    #[Test]
    public function toArray_handles_complex_country_names(): void
    {
        // Arrange
        $countryData = array_merge($this->getResourceData(), [
            'code' => 'VA',
            'name' => 'Vatican City State',
            'currency_uuid' => $this->generateTestUuid(),
            'currency_code' => 'EUR',
            'currency_symbol' => '€',
            'locale' => 'it_VA',
        ]);
        $country = $this->createMockModel($countryData);
        $request = new Request();

        // Act
        $resource = new CountryResource($country);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('Vatican City State', $result['name']);
        $this->assertEquals('VA', $result['code']);
        $this->assertEquals('EUR', $result['currency_code']);
        $this->assertEquals('€', $result['currency_symbol']);
    }

    #[Test]
    public function toArray_validates_uuid_preservation(): void
    {
        // Arrange
        $testUuid = 'f47ac10b-58cc-4372-a567-0e02b2c3d479';
        $countryData = array_merge($this->getResourceData(), [
            'uuid' => $testUuid,
        ]);
        $country = $this->createMockModel($countryData);
        $request = new Request();

        // Act
        $resource = new CountryResource($country);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals($testUuid, $result['uuid']);
        $this->assertIsString($result['uuid']);
    }

    #[Test]
    public function toArray_handles_inactive_countries(): void
    {
        // Arrange
        $countryData = array_merge($this->getResourceData(), [
            'is_active' => false,
        ]);
        $country = $this->createMockModel($countryData);
        $request = new Request();

        // Act
        $resource = new CountryResource($country);
        $result = $resource->toArray($request);

        // Assert
        $this->assertFalse($result['is_active']);
        $this->assertIsBool($result['is_active']);
    }

    #[Test]
    public function toArray_handles_special_currency_symbols(): void
    {
        // Arrange
        $countryData = array_merge($this->getResourceData(), [
            'code' => 'JP',
            'name' => 'Japan',
            'currency_uuid' => $this->generateTestUuid(),
            'currency_code' => 'JPY',
            'currency_symbol' => '¥',
            'locale' => 'ja_JP',
        ]);
        $country = $this->createMockModel($countryData);
        $request = new Request();

        // Act
        $resource = new CountryResource($country);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('¥', $result['currency_symbol']);
        $this->assertEquals('JPY', $result['currency_code']);
        $this->assertEquals('ja_JP', $result['locale']);
    }
}