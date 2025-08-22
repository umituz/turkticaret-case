<?php

namespace Tests\Unit\Http\Resources\Currency;

use App\Http\Resources\Currency\CurrencyResource;
use Tests\Base\BaseResourceUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Unit tests for CurrencyResource
 * Tests currency response formatting and structure
 */
#[CoversClass(CurrencyResource::class)]
#[Group('unit')]
#[Group('resources')]
#[Small]
class CurrencyResourceTest extends BaseResourceUnitTest
{
    protected function getResourceClass(): string
    {
        return CurrencyResource::class;
    }

    protected function getResourceData(): array
    {
        return [
            'uuid' => $this->generateTestUuid(),
            'code' => 'USD',
            'name' => 'US Dollar',
            'symbol' => '$',
            'decimals' => 2,
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
        $currencyData = $this->getResourceData();
        $currency = $this->createMockModel($currencyData);
        $request = new Request();

        // Act
        $resource = new CurrencyResource($currency);
        $result = $resource->toArray($request);

        // Assert
        $this->assertResourceArrayStructure([
            'uuid',
            'code',
            'name',
            'symbol',
            'decimals',
            'is_active',
            'created_at',
            'updated_at',
        ], $result);

        $this->assertEquals($currencyData['uuid'], $result['uuid']);
        $this->assertEquals($currencyData['code'], $result['code']);
        $this->assertEquals($currencyData['name'], $result['name']);
        $this->assertEquals($currencyData['symbol'], $result['symbol']);
        $this->assertEquals($currencyData['decimals'], $result['decimals']);
    }

    #[Test]
    public function toArray_includes_all_currency_fields(): void
    {
        // Arrange
        $currencyData = [
            'uuid' => 'currency-test-uuid',
            'code' => 'EUR',
            'name' => 'Euro',
            'symbol' => '€',
            'decimals' => 2,
            'is_active' => true,
            'created_at' => Carbon::parse('2024-01-01 10:00:00'),
            'updated_at' => Carbon::parse('2024-01-15 14:30:00'),
        ];
        $currency = $this->createMockModel($currencyData);
        $request = new Request();

        // Act
        $resource = new CurrencyResource($currency);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('currency-test-uuid', $result['uuid']);
        $this->assertEquals('EUR', $result['code']);
        $this->assertEquals('Euro', $result['name']);
        $this->assertEquals('€', $result['symbol']);
        $this->assertEquals(2, $result['decimals']);
        $this->assertTrue($result['is_active']);
    }

    #[Test]
    public function toArray_formats_timestamps_as_iso_string(): void
    {
        // Arrange
        $createdAt = Carbon::parse('2024-01-01 12:00:00');
        $updatedAt = Carbon::parse('2024-01-15 15:30:00');
        $currencyData = array_merge($this->getResourceData(), [
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ]);
        $currency = $this->createMockModel($currencyData);
        $request = new Request();

        // Act
        $resource = new CurrencyResource($currency);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals($createdAt->toIso8601String(), $result['created_at']);
        $this->assertEquals($updatedAt->toIso8601String(), $result['updated_at']);
    }

    #[Test]
    public function toArray_handles_null_timestamps(): void
    {
        // Arrange
        $currencyData = array_merge($this->getResourceData(), [
            'created_at' => null,
            'updated_at' => null,
        ]);
        $currency = $this->createMockModel($currencyData);
        $request = new Request();

        // Act
        $resource = new CurrencyResource($currency);
        $result = $resource->toArray($request);

        // Assert
        $this->assertNull($result['created_at']);
        $this->assertNull($result['updated_at']);
    }

    #[Test]
    public function toArray_handles_different_decimal_places(): void
    {
        // Arrange
        $currencyData = array_merge($this->getResourceData(), [
            'code' => 'JPY',
            'name' => 'Japanese Yen',
            'symbol' => '¥',
            'decimals' => 0,
        ]);
        $currency = $this->createMockModel($currencyData);
        $request = new Request();

        // Act
        $resource = new CurrencyResource($currency);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('JPY', $result['code']);
        $this->assertEquals('Japanese Yen', $result['name']);
        $this->assertEquals('¥', $result['symbol']);
        $this->assertEquals(0, $result['decimals']);
        $this->assertIsInt($result['decimals']);
    }


    #[Test]
    public function toArray_handles_special_currency_symbols(): void
    {
        // Arrange
        $currencyData = array_merge($this->getResourceData(), [
            'code' => 'GBP',
            'name' => 'British Pound Sterling',
            'symbol' => '£',
            'decimals' => 2,
        ]);
        $currency = $this->createMockModel($currencyData);
        $request = new Request();

        // Act
        $resource = new CurrencyResource($currency);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('£', $result['symbol']);
        $this->assertEquals('GBP', $result['code']);
        $this->assertEquals('British Pound Sterling', $result['name']);
    }

    #[Test]
    public function toArray_validates_uuid_preservation(): void
    {
        // Arrange
        $testUuid = 'f47ac10b-58cc-4372-a567-0e02b2c3d479';
        $currencyData = array_merge($this->getResourceData(), [
            'uuid' => $testUuid,
        ]);
        $currency = $this->createMockModel($currencyData);
        $request = new Request();

        // Act
        $resource = new CurrencyResource($currency);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals($testUuid, $result['uuid']);
        $this->assertIsString($result['uuid']);
    }

    #[Test]
    public function toArray_handles_inactive_currencies(): void
    {
        // Arrange
        $currencyData = array_merge($this->getResourceData(), [
            'is_active' => false,
        ]);
        $currency = $this->createMockModel($currencyData);
        $request = new Request();

        // Act
        $resource = new CurrencyResource($currency);
        $result = $resource->toArray($request);

        // Assert
        $this->assertFalse($result['is_active']);
        $this->assertIsBool($result['is_active']);
    }

    #[Test]
    public function toArray_handles_cryptocurrency(): void
    {
        // Arrange
        $currencyData = array_merge($this->getResourceData(), [
            'code' => 'BTC',
            'name' => 'Bitcoin',
            'symbol' => '₿',
            'decimals' => 8,
        ]);
        $currency = $this->createMockModel($currencyData);
        $request = new Request();

        // Act
        $resource = new CurrencyResource($currency);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('BTC', $result['code']);
        $this->assertEquals('Bitcoin', $result['name']);
        $this->assertEquals('₿', $result['symbol']);
        $this->assertEquals(8, $result['decimals']);
    }

    #[Test]
    public function toArray_handles_high_precision_currencies(): void
    {
        // Arrange
        $currencyData = array_merge($this->getResourceData(), [
            'code' => 'ETH',
            'name' => 'Ethereum',
            'symbol' => 'Ξ',
            'decimals' => 18,
        ]);
        $currency = $this->createMockModel($currencyData);
        $request = new Request();

        // Act
        $resource = new CurrencyResource($currency);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals(18, $result['decimals']);
        $this->assertEquals('ETH', $result['code']);
        $this->assertEquals('Ξ', $result['symbol']);
    }

    #[Test]
    public function toArray_handles_complex_currency_names(): void
    {
        // Arrange
        $currencyData = array_merge($this->getResourceData(), [
            'code' => 'TRY',
            'name' => 'Turkish Lira',
            'symbol' => '₺',
            'decimals' => 2,
        ]);
        $currency = $this->createMockModel($currencyData);
        $request = new Request();

        // Act
        $resource = new CurrencyResource($currency);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('Turkish Lira', $result['name']);
        $this->assertEquals('₺', $result['symbol']);
        $this->assertEquals('TRY', $result['code']);
    }

    #[Test]
    public function toArray_preserves_decimal_precision(): void
    {
        // Arrange
        $currencyData = array_merge($this->getResourceData(), [
            'decimals' => 3,
        ]);
        $currency = $this->createMockModel($currencyData);
        $request = new Request();

        // Act
        $resource = new CurrencyResource($currency);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals(3, $result['decimals']);
        $this->assertIsInt($result['decimals']);
    }
}