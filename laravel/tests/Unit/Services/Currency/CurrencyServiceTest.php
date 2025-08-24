<?php

namespace Tests\Unit\Services\Currency;

use App\Services\Currency\CurrencyService;
use App\Repositories\Currency\CurrencyRepositoryInterface;
use App\Models\Currency\Currency;
use Tests\Base\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Mockery;
use Mockery\MockInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Unit tests for CurrencyService
 * Tests currency management operations with repository mocking
 */
#[CoversClass(CurrencyService::class)]
#[Group('unit')]
#[Group('services')]
#[Small]
class CurrencyServiceTest extends UnitTestCase
{
    private CurrencyService $service;
    private CurrencyRepositoryInterface|MockInterface $currencyRepository;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->currencyRepository = Mockery::mock(CurrencyRepositoryInterface::class);
        $this->service = new CurrencyService($this->currencyRepository);
    }

    #[Test]
    public function get_all_currencies_returns_collection_from_repository(): void
    {
        // Arrange
        $expectedCollection = Mockery::mock(Collection::class);
        $this->currencyRepository
            ->shouldReceive('all')
            ->once()
            ->andReturn($expectedCollection);

        // Act
        $result = $this->service->getAllCurrencies();

        // Assert
        $this->assertSame($expectedCollection, $result);
    }

    #[Test]
    public function create_currency_creates_currency_through_repository(): void
    {
        // Arrange
        $currencyData = [
            'name' => 'Turkish Lira',
            'code' => 'TRY',
            'symbol' => '₺',
            'exchange_rate' => 1.00
        ];
        $expectedCurrency = Mockery::mock(Currency::class);

        $this->currencyRepository
            ->shouldReceive('create')
            ->once()
            ->with($currencyData)
            ->andReturn($expectedCurrency);

        // Act
        $result = $this->service->createCurrency($currencyData);

        // Assert
        $this->assertSame($expectedCurrency, $result);
    }

    #[Test]
    public function update_currency_updates_currency_by_uuid(): void
    {
        // Arrange
        $uuid = 'currency-uuid';
        $updateData = [
            'name' => 'New Turkish Lira',
            'exchange_rate' => 0.85
        ];
        $expectedCurrency = Mockery::mock(Currency::class);

        $this->currencyRepository
            ->shouldReceive('updateByUuid')
            ->once()
            ->with($uuid, $updateData)
            ->andReturn($expectedCurrency);

        // Act
        $result = $this->service->updateCurrency($uuid, $updateData);

        // Assert
        $this->assertSame($expectedCurrency, $result);
    }

    #[Test]
    public function delete_currency_deletes_currency_by_uuid(): void
    {
        // Arrange
        $uuid = 'currency-uuid';
        
        $this->currencyRepository
            ->shouldReceive('deleteByUuid')
            ->once()
            ->with($uuid)
            ->andReturn(true);

        // Act
        $result = $this->service->deleteCurrency($uuid);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function delete_currency_returns_false_when_deletion_fails(): void
    {
        // Arrange
        $uuid = 'non-existent-uuid';
        
        $this->currencyRepository
            ->shouldReceive('deleteByUuid')
            ->once()
            ->with($uuid)
            ->andReturn(false);

        // Act
        $result = $this->service->deleteCurrency($uuid);

        // Assert
        $this->assertFalse($result);
    }
}