<?php

namespace Tests\Unit\Services\Country;

use App\Services\Country\CountryService;
use App\Repositories\Country\CountryRepositoryInterface;
use App\Models\Country\Country;
use Tests\Base\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Mockery;
use Mockery\MockInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Unit tests for CountryService
 * Tests country management operations with repository mocking
 */
#[CoversClass(CountryService::class)]
#[Group('unit')]
#[Group('services')]
#[Small]
class CountryServiceTest extends UnitTestCase
{
    private CountryService $service;
    private CountryRepositoryInterface|MockInterface $countryRepository;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->countryRepository = Mockery::mock(CountryRepositoryInterface::class);
        $this->service = new CountryService($this->countryRepository);
    }

    #[Test]
    public function get_all_countries_returns_collection_from_repository(): void
    {
        // Arrange
        $expectedCollection = Mockery::mock(Collection::class);
        $this->countryRepository
            ->shouldReceive('all')
            ->once()
            ->andReturn($expectedCollection);

        // Act
        $result = $this->service->getAllCountries();

        // Assert
        $this->assertSame($expectedCollection, $result);
    }

    #[Test]
    public function create_country_creates_country_through_repository(): void
    {
        // Arrange
        $countryData = [
            'name' => 'Turkey',
            'code' => 'TR',
            'iso_code' => 'TUR'
        ];
        $expectedCountry = Mockery::mock(Country::class);

        $this->countryRepository
            ->shouldReceive('create')
            ->once()
            ->with($countryData)
            ->andReturn($expectedCountry);

        // Act
        $result = $this->service->createCountry($countryData);

        // Assert
        $this->assertSame($expectedCountry, $result);
    }

    #[Test]
    public function update_country_updates_country_by_uuid(): void
    {
        // Arrange
        $uuid = 'country-uuid';
        $updateData = [
            'name' => 'Republic of Turkey',
            'code' => 'TR'
        ];
        $expectedCountry = Mockery::mock(Country::class);

        $this->countryRepository
            ->shouldReceive('updateByUuid')
            ->once()
            ->with($uuid, $updateData)
            ->andReturn($expectedCountry);

        // Act
        $result = $this->service->updateCountry($uuid, $updateData);

        // Assert
        $this->assertSame($expectedCountry, $result);
    }

    #[Test]
    public function delete_country_deletes_country_by_uuid(): void
    {
        // Arrange
        $uuid = 'country-uuid';
        
        $this->countryRepository
            ->shouldReceive('deleteByUuid')
            ->once()
            ->with($uuid)
            ->andReturn(true);

        // Act
        $result = $this->service->deleteCountry($uuid);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function find_by_code_returns_country_when_found(): void
    {
        // Arrange
        $countryCode = 'TR';
        $expectedCountry = Mockery::mock(Country::class);

        $this->countryRepository
            ->shouldReceive('findByCode')
            ->once()
            ->with($countryCode)
            ->andReturn($expectedCountry);

        // Act
        $result = $this->service->findByCode($countryCode);

        // Assert
        $this->assertSame($expectedCountry, $result);
    }

    #[Test]
    public function find_by_code_throws_exception_when_country_not_found(): void
    {
        // Arrange
        $countryCode = 'XX';

        $this->countryRepository
            ->shouldReceive('findByCode')
            ->once()
            ->with($countryCode)
            ->andReturn(null);

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid country code provided');
        
        $this->service->findByCode($countryCode);
    }

    #[Test]
    public function find_by_code_handles_empty_result_correctly(): void
    {
        // Arrange
        $countryCode = 'INVALID';

        $this->countryRepository
            ->shouldReceive('findByCode')
            ->once()
            ->with($countryCode)
            ->andReturn(null);

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid country code provided');
        
        $this->service->findByCode($countryCode);
    }
}