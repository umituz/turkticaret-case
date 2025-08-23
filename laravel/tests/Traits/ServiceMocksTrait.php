<?php

namespace Tests\Traits;

use Mockery;
use Mockery\MockInterface;

/**
 * Trait providing common mocking utilities for service unit tests
 */
trait ServiceMocksTrait
{
    /**
     * Mock a repository interface
     */
    protected function mockRepository(string $repositoryInterface): MockInterface
    {
        return $this->mock($repositoryInterface);
    }

    /**
     * Mock a service class
     */
    protected function mockService(string $serviceClass): MockInterface
    {
        return $this->mock($serviceClass);
    }

    /**
     * Mock repository with common CRUD methods
     */
    protected function mockRepositoryWithCrud(string $repositoryInterface): MockInterface
    {
        $mock = $this->mockRepository($repositoryInterface);
        
        $mock->shouldReceive('create')->andReturnUsing(function ($data) {
            $model = Mockery::mock();
            $model->shouldReceive('getAttribute')->andReturnUsing(function ($key) use ($data) {
                return $data[$key] ?? null;
            });
            foreach ($data as $key => $value) {
                $model->$key = $value;
            }
            return $model;
        });
        
        $mock->shouldReceive('find')->andReturnNull();
        $mock->shouldReceive('findByUuid')->andReturnNull();
        $mock->shouldReceive('update')->andReturn(true);
        $mock->shouldReceive('delete')->andReturn(true);
        
        return $mock;
    }

    /**
     * Mock service with validation capabilities
     */
    protected function mockServiceWithValidation(string $serviceClass): MockInterface
    {
        $mock = $this->mockService($serviceClass);
        
        $mock->shouldReceive('validate')->andReturn(true);
        $mock->shouldReceive('validateExists')->andReturn(true);
        
        return $mock;
    }

    /**
     * Create a mock model instance
     */
    protected function mockModel(array $attributes = []): MockInterface
    {
        $model = Mockery::mock();
        
        // Add default UUID
        $attributes['uuid'] = $attributes['uuid'] ?? $this->generateTestUuid();
        
        foreach ($attributes as $key => $value) {
            $model->shouldReceive('getAttribute')->with($key)->andReturn($value);
            $model->shouldReceive('setAttribute')->with($key, Mockery::any())->andReturnSelf();
            $model->$key = $value;
        }
        
        // Mock common model methods
        $model->shouldReceive('save')->andReturn(true);
        $model->shouldReceive('delete')->andReturn(true);
        $model->shouldReceive('load')->andReturnSelf();
        $model->shouldReceive('with')->andReturnSelf();
        $model->shouldReceive('toArray')->andReturn($attributes);
        $model->shouldReceive('fresh')->andReturnSelf();
        $model->shouldReceive('refresh')->andReturnSelf();
        
        return $model;
    }

    /**
     * Create a typed mock model instance
     */
    protected function mockTypedModel(string $modelClass, array $attributes = []): MockInterface
    {
        $model = Mockery::mock($modelClass);
        
        // Add default UUID
        $attributes['uuid'] = $attributes['uuid'] ?? $this->generateTestUuid();
        
        foreach ($attributes as $key => $value) {
            $model->shouldReceive('getAttribute')->with($key)->andReturn($value);
            $model->shouldReceive('setAttribute')->with($key, Mockery::any())->andReturnSelf();
            $model->$key = $value;
        }
        
        // Mock __get method for property access
        $model->shouldReceive('__get')->andReturnUsing(function ($key) use ($attributes) {
            return $attributes[$key] ?? null;
        });
        
        // Mock direct property access via magic __isset and __get
        $model->shouldReceive('__isset')->andReturnUsing(function ($key) use ($attributes) {
            return array_key_exists($key, $attributes);
        });
        
        // Mock offsetExists for ArrayAccess interface
        $model->shouldReceive('offsetExists')->andReturnUsing(function ($key) use ($attributes) {
            return array_key_exists($key, $attributes);
        });
        
        // Mock common model methods
        $model->shouldReceive('save')->andReturn(true);
        $model->shouldReceive('delete')->andReturn(true);
        $model->shouldReceive('load')->andReturnSelf();
        $model->shouldReceive('with')->andReturnSelf();
        $model->shouldReceive('toArray')->andReturn($attributes);
        $model->shouldReceive('fresh')->andReturnSelf();
        $model->shouldReceive('refresh')->andReturnSelf();
        
        return $model;
    }

    /**
     * Mock collection with models
     */
    protected function mockCollection(array $models = []): MockInterface
    {
        $collection = Mockery::mock();
        $collection->shouldReceive('isEmpty')->andReturn(empty($models));
        $collection->shouldReceive('count')->andReturn(count($models));
        $collection->shouldReceive('all')->andReturn($models);
        $collection->shouldReceive('sum')->andReturnUsing(function ($key) use ($models) {
            return array_sum(array_column($models, $key));
        });
        
        return $collection;
    }

    /**
     * Mock a HasMany relationship for a model
     */
    protected function mockHasManyRelation(array $config = []): MockInterface
    {
        $relation = Mockery::mock(\Illuminate\Database\Eloquent\Relations\HasMany::class);
        
        // Mock builder methods that return self
        $relation->shouldReceive('where')->andReturnSelf();
        $relation->shouldReceive('whereIn')->andReturnSelf();
        $relation->shouldReceive('orderBy')->andReturnSelf();
        $relation->shouldReceive('limit')->andReturnSelf();
        $relation->shouldReceive('offset')->andReturnSelf();
        
        // Mock methods that return results (with configurable defaults)
        $relation->shouldReceive('first')->andReturn($config['first'] ?? null);
        $relation->shouldReceive('get')->andReturn($config['get'] ?? collect([]));
        $relation->shouldReceive('count')->andReturn($config['count'] ?? 0);
        $relation->shouldReceive('exists')->andReturn($config['exists'] ?? false);
        
        // Mock CRUD methods
        $relation->shouldReceive('create')->andReturn($config['create'] ?? null);
        $relation->shouldReceive('delete')->andReturn($config['delete'] ?? true);
        $relation->shouldReceive('update')->andReturn($config['update'] ?? true);
        
        return $relation;
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
     * Mock DTO object
     */
    protected function mockDTO(string $dtoClass, array $data = []): MockInterface
    {
        $dto = Mockery::mock($dtoClass);
        
        foreach ($data as $property => $value) {
            $dto->$property = $value;
            $dto->shouldReceive('getAttribute')->with($property)->andReturn($value);
        }
        
        return $dto;
    }

    /**
     * Setup transaction mocking for database operations
     */
    protected function mockDatabaseTransaction(): void
    {
        // Transaction is already mocked in UnitTestCase, but we can override if needed
        $this->app['db']->shouldReceive('transaction')->andReturnUsing(function ($callback) {
            return $callback();
        });
    }

    /**
     * Mock exception throwing
     */
    protected function mockException(string $exceptionClass, string $message = 'Test exception'): MockInterface
    {
        $exception = Mockery::mock($exceptionClass);
        $exception->shouldReceive('getMessage')->andReturn($message);
        
        return $exception;
    }

    /**
     * Create test user UUID consistently
     */
    protected function getTestUserUuid(): string
    {
        return 'test-user-uuid-12345';
    }

    /**
     * Create test entity UUID consistently
     */
    protected function getTestEntityUuid(): string
    {
        return 'test-entity-uuid-12345';
    }
}