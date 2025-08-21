<?php

namespace Tests\Traits;

use Mockery;
use Mockery\MockInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

/**
 * Trait providing common mocking utilities for repository unit tests
 */
trait RepositoryMocksTrait
{
    /**
     * Mock an Eloquent model class
     */
    protected function mockModel(string $modelClass): MockInterface
    {
        $model = Mockery::mock($modelClass);
        
        // Mock common Eloquent model methods
        $model->shouldReceive('newQuery')->andReturnSelf();
        $model->shouldReceive('where')->andReturnSelf();
        $model->shouldReceive('with')->andReturnSelf();
        $model->shouldReceive('orderBy')->andReturnSelf();
        $model->shouldReceive('take')->andReturnSelf();
        $model->shouldReceive('paginate')->andReturn($this->mockPaginator());
        $model->shouldReceive('get')->andReturn($this->mockCollection());
        // Allow first() to be mocked by individual tests
        $model->shouldReceive('firstOrFail')->andReturn($model);
        $model->shouldReceive('firstOrCreate')->andReturn($model);
        $model->shouldReceive('create')->andReturn($model);
        $model->shouldReceive('insert')->andReturn(true);
        $model->shouldReceive('update')->andReturn(true);
        $model->shouldReceive('delete')->andReturn(true);
        $model->shouldReceive('restore')->andReturn(true);
        $model->shouldReceive('forceDelete')->andReturn(true);
        $model->shouldReceive('exists')->andReturn(false);
        $model->shouldReceive('count')->andReturn(0);
        
        // Mock attributes
        $model->shouldReceive('setAttribute')->andReturnSelf();
        $model->shouldReceive('getAttribute')->with('uuid')->andReturn($this->generateTestUuid());
        $model->uuid = $this->generateTestUuid();
        
        return $model;
    }

    /**
     * Mock query builder
     */
    protected function mockQueryBuilder(): MockInterface
    {
        $builder = Mockery::mock(Builder::class);
        
        $builder->shouldReceive('where')->andReturnSelf();
        $builder->shouldReceive('with')->andReturnSelf();
        $builder->shouldReceive('orderBy')->andReturnSelf();
        $builder->shouldReceive('take')->andReturnSelf();
        $builder->shouldReceive('paginate')->andReturn($this->mockPaginator());
        $builder->shouldReceive('get')->andReturn($this->mockCollection());
        // Allow first() to be mocked by individual tests
        $builder->shouldReceive('firstOrFail')->andReturn(Mockery::mock(Model::class));
        $builder->shouldReceive('delete')->andReturn(true);
        $builder->shouldReceive('restore')->andReturn(true);
        $builder->shouldReceive('exists')->andReturn(false);
        $builder->shouldReceive('count')->andReturn(0);
        
        return $builder;
    }

    /**
     * Mock Eloquent collection
     */
    protected function mockCollection(array $items = []): MockInterface
    {
        $collection = Mockery::mock(Collection::class);
        
        $collection->shouldReceive('count')->andReturn(count($items));
        $collection->shouldReceive('isEmpty')->andReturn(empty($items));
        $collection->shouldReceive('isNotEmpty')->andReturn(!empty($items));
        $collection->shouldReceive('all')->andReturn($items);
        $collection->shouldReceive('toArray')->andReturn($items);
        
        return $collection;
    }

    /**
     * Mock Laravel paginator
     */
    protected function mockPaginator(array $items = [], int $total = null): MockInterface
    {
        $paginator = Mockery::mock(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class);
        $total = $total ?? count($items);
        
        $paginator->shouldReceive('items')->andReturn($items);
        $paginator->shouldReceive('total')->andReturn($total);
        $paginator->shouldReceive('count')->andReturn(count($items));
        $paginator->shouldReceive('currentPage')->andReturn(1);
        $paginator->shouldReceive('lastPage')->andReturn(1);
        $paginator->shouldReceive('perPage')->andReturn(15);
        
        return $paginator;
    }

    /**
     * Mock model instance with attributes
     */
    protected function mockModelInstance(string $modelClass, array $attributes = []): MockInterface
    {
        $model = Mockery::mock($modelClass);
        
        $attributes['uuid'] = $attributes['uuid'] ?? $this->generateTestUuid();
        
        foreach ($attributes as $key => $value) {
            $model->shouldReceive('getAttribute')->with($key)->andReturn($value);
            $model->shouldReceive('setAttribute')->with($key, Mockery::any())->andReturnSelf();
            $model->$key = $value;
        }
        
        $model->shouldReceive('update')->andReturn(true);
        $model->shouldReceive('delete')->andReturn(true);
        $model->shouldReceive('forceDelete')->andReturn(true);
        $model->shouldReceive('restore')->andReturn(true);
        $model->shouldReceive('save')->andReturn(true);
        $model->shouldReceive('toArray')->andReturn($attributes);
        
        return $model;
    }

    /**
     * Setup transaction mocks
     */
    protected function mockDatabaseTransaction(): void
    {
        $this->app['db']->shouldReceive('beginTransaction')->andReturn(true);
        $this->app['db']->shouldReceive('commit')->andReturn(true);
        $this->app['db']->shouldReceive('rollBack')->andReturn(true);
    }

    /**
     * Mock model not found exception
     */
    protected function mockModelNotFoundException(): void
    {
        $exception = new \Illuminate\Database\Eloquent\ModelNotFoundException();
        throw $exception;
    }

    /**
     * Create test entity UUID consistently  
     */
    protected function getTestEntityUuid(): string
    {
        return 'test-entity-uuid-12345';
    }

    /**
     * Create test user UUID consistently
     */
    protected function getTestUserUuid(): string
    {
        return 'test-user-uuid-12345';
    }

    /**
     * Create multiple test UUIDs
     */
    protected function generateMultipleTestUuids(int $count): array
    {
        $uuids = [];
        for ($i = 0; $i < $count; $i++) {
            $uuids[] = $this->generateTestUuid();
        }
        return $uuids;
    }
}