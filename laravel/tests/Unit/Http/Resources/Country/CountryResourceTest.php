<?php

namespace Tests\Unit\Http\Resources\Country;

use App\Http\Resources\Country\CountryResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Tests\Base\BaseResourceUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

/**
 * Unit tests for CountryResource
 * Tests country resource transformation with original fields only
 */
#[CoversClass(CountryResource::class)]
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
    public function to_array_returns_correct_structure(): void
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
            'currency_uuid',
            'locale',
            'is_active',
            'currency',
            'created_at',
            'updated_at',
        ], $result);

        $this->assertEquals($countryData['uuid'], $result['uuid']);
        $this->assertEquals($countryData['code'], $result['code']);
        $this->assertEquals($countryData['name'], $result['name']);
        $this->assertEquals($countryData['currency_uuid'], $result['currency_uuid']);
        $this->assertEquals($countryData['locale'], $result['locale']);
    }

    #[Test]
    public function to_array_includes_all_country_fields(): void
    {
        // Arrange
        $countryData = [
            'uuid' => 'country-test-uuid',
            'code' => 'TR',
            'name' => 'Turkey',
            'currency_uuid' => $this->generateTestUuid(),
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
        $this->assertEquals($countryData['currency_uuid'], $result['currency_uuid']);
        $this->assertEquals('tr_TR', $result['locale']);
    }

    #[Test]
    public function to_array_formats_timestamps_as_iso_string(): void
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
    public function to_array_handles_null_timestamps(): void
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
    public function to_array_handles_null_optional_fields(): void
    {
        // Arrange
        $countryData = array_merge($this->getResourceData(), [
            'currency_uuid' => null,
            'locale' => null,
        ]);
        $country = $this->createMockModel($countryData);
        $request = new Request();

        // Act
        $resource = new CountryResource($country);
        $result = $resource->toArray($request);

        // Assert
        $this->assertArrayHasKey('currency_uuid', $result);
        $this->assertArrayHasKey('locale', $result);
        $this->assertNull($result['currency_uuid']);
        $this->assertNull($result['locale']);
    }

    #[Test]
    public function to_array_preserves_country_code_format(): void
    {
        // Arrange
        $countryData = array_merge($this->getResourceData(), [
            'code' => 'GB',
        ]);
        $country = $this->createMockModel($countryData);
        $request = new Request();

        // Act
        $resource = new CountryResource($country);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('GB', $result['code']);
        $this->assertIsString($result['code']);
    }

    #[Test]
    public function to_array_validates_uuid_preservation(): void
    {
        // Arrange
        $testUuid = $this->generateTestUuid();
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
    public function to_array_handles_inactive_countries(): void
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
}