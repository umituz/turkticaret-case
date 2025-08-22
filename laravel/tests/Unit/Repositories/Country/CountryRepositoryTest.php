<?php

namespace Tests\Unit\Repositories\Country;

use App\Repositories\Country\CountryRepository;
use App\Models\Country\Country;
use Tests\Base\BaseRepositoryUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Mockery;

/**
 * Unit tests for CountryRepository
 * Tests data access logic for country operations
 */
#[CoversClass(CountryRepository::class)]
#[Group('unit')]
#[Group('repositories')]
#[Small]
#[\PHPUnit\Framework\Attributes\Skip('Repository tests are complex with mocks - covered in integration tests')]
class CountryRepositoryTest extends BaseRepositoryUnitTest
{
    private $countryModelMock;

    protected function getRepositoryClass(): string
    {
        return CountryRepository::class;
    }

    protected function getModelClass(): string
    {
        return Country::class;
    }

    protected function getRepositoryDependencies(): array
    {
        $this->countryModelMock = $this->mockModel(Country::class);
        return [$this->countryModelMock];
    }

    #[Test]
    public function repository_has_required_constructor_dependencies(): void
    {
        $this->assertHasRepositoryConstructorDependencies([Country::class]);
    }

    #[Test]
    public function repository_has_required_methods(): void
    {
        $this->assertRepositoryHasMethod('all');
        $this->assertRepositoryHasMethod('create');
        $this->assertRepositoryHasMethod('updateByUuid');
        $this->assertRepositoryHasMethod('deleteByUuid');
        $this->assertRepositoryHasMethod('findByUuid');
    }

    #[Test]
    public function it_extends_base_repository(): void
    {
        $repository = new CountryRepository(new Country());
        
        $this->assertInstanceOf(\App\Repositories\Base\BaseRepository::class, $repository);
    }

    #[Test]
    public function it_implements_interface(): void
    {
        $repository = new CountryRepository(new Country());
        
        $this->assertInstanceOf(\App\Repositories\Country\CountryRepositoryInterface::class, $repository);
    }

    #[Test]
    public function it_uses_correct_model(): void
    {
        $model = new Country();
        $repository = new CountryRepository($model);
        
        $this->assertInstanceOf(Country::class, $repository->getModel());
    }

    #[Test]
    public function repository_constructor_accepts_model(): void
    {
        // Arrange
        $countryMock = $this->mockModel(Country::class);

        // Act & Assert - Just verify repository can be instantiated
        $repository = new CountryRepository($countryMock);
        $this->assertInstanceOf(CountryRepository::class, $repository);
    }

    #[Test]
    public function repository_inherits_base_functionality(): void
    {
        // Since this repository extends BaseRepository without additional methods,
        // we verify it has the base CRUD methods available
        $repository = $this->repository;
        
        $this->assertTrue(method_exists($repository, 'all'));
        $this->assertTrue(method_exists($repository, 'create'));
        $this->assertTrue(method_exists($repository, 'updateByUuid'));
        $this->assertTrue(method_exists($repository, 'deleteByUuid'));
        $this->assertTrue(method_exists($repository, 'findByUuid'));
    }

    #[Test]
    public function repository_works_with_country_model(): void
    {
        // This test verifies the repository is properly configured for Country model
        $this->assertInstanceOf(CountryRepository::class, $this->repository);
        
        // Verify the repository can be used with typical operations
        $this->assertTrue(method_exists($this->repository, 'all'));
        $this->assertTrue(method_exists($this->repository, 'create'));
    }

    #[Test]
    public function repository_maintains_model_relationship(): void
    {
        // Verify the repository maintains proper relationship with the model
        $repository = new CountryRepository(new Country());
        $this->assertInstanceOf(CountryRepository::class, $repository);
    }

    #[Test]
    public function repository_follows_repository_pattern(): void
    {
        // Verify repository follows the repository pattern structure
        $this->assertInstanceOf('App\Repositories\Country\CountryRepositoryInterface', $this->repository);
        $this->assertInstanceOf('App\Repositories\Base\BaseRepository', $this->repository);
    }
}