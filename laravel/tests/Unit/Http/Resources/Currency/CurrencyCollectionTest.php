<?php

namespace Tests\Unit\Http\Resources\Currency;

use App\Http\Resources\Currency\CurrencyCollection;
use App\Http\Resources\Currency\CurrencyResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Tests\Base\BaseResourceUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;

/**
 * Unit tests for CurrencyCollection
 * Tests currency collection functionality with original BaseCollection
 */
#[CoversClass(CurrencyCollection::class)]
#[Group('unit')]
#[Group('resources')]
#[Small]
class CurrencyCollectionTest extends BaseResourceUnitTest
{
    protected function getResourceClass(): string
    {
        return CurrencyCollection::class;
    }

    protected function getResourceData(): array
    {
        return [
            [
                'uuid' => $this->generateTestUuid(),
                'name' => 'US Dollar',
                'code' => 'USD',
                'symbol' => '$',
                'decimal_places' => 2,
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'uuid' => $this->generateTestUuid(),
                'name' => 'Turkish Lira',
                'code' => 'TRY',
                'symbol' => '₺',
                'decimal_places' => 2,
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
        $collection = new CurrencyCollection([]);

        // Assert
        $this->assertEquals(CurrencyResource::class, $collection->collects);
    }

    #[Test]
    public function collection_transforms_currencies_correctly(): void
    {
        // Arrange
        $currencyData = $this->getResourceData();
        $currencies = array_map(fn($data) => $this->createMockModel($data), $currencyData);
        $paginator = $this->createMockPaginatedCollection($currencies, 2);
        $request = new Request();

        // Act
        $collection = new CurrencyCollection($paginator);
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
        $currencies = [
            $this->createMockModel(['uuid' => 'curr-1', 'name' => 'US Dollar', 'code' => 'USD', 'is_active' => true]),
            $this->createMockModel(['uuid' => 'curr-2', 'name' => 'Euro', 'code' => 'EUR', 'is_active' => true]),
        ];
        $totalCurrencies = 25;
        $paginator = $this->createMockPaginatedCollection($currencies, $totalCurrencies);
        $request = new Request();

        // Act
        $collection = new CurrencyCollection($paginator);
        $result = $collection->toArray($request);

        // Assert
        $meta = $result['meta'];
        $this->assertEquals($totalCurrencies, $meta['total']);
        $this->assertEquals(count($currencies), $meta['count']);
        $this->assertArrayHasKey('current_page', $meta);
        $this->assertArrayHasKey('last_page', $meta);
        $this->assertArrayHasKey('per_page', $meta);
    }

    #[Test]
    public function collection_handles_empty_currency_list(): void
    {
        // Arrange
        $paginator = $this->createMockPaginatedCollection([], 0);
        $request = new Request();

        // Act
        $collection = new CurrencyCollection($paginator);
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