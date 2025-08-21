<?php

namespace Tests\Traits;

use Mockery;

/**
 * Trait providing common assertions for repository unit tests
 */
trait RepositoryAssertionsTrait
{
    /**
     * Assert that repository has a specific method
     */
    protected function assertRepositoryHasMethod(string $method): void
    {
        $this->assertTrue(
            method_exists($this->repository, $method),
            "Repository does not have method: {$method}"
        );
    }

    /**
     * Assert that repository method was called on the model
     */
    protected function assertRepositoryUsesModel($modelMock, string $method, array $parameters = []): void
    {
        if (empty($parameters)) {
            $modelMock->shouldReceive($method)->once()->andReturn(null);
        } else {
            $modelMock->shouldReceive($method)->once()->with(...$parameters)->andReturn(null);
        }
    }

    /**
     * Assert that repository has expected constructor dependencies
     */
    protected function assertHasRepositoryConstructorDependencies(array $expectedDependencies): void
    {
        $reflection = new \ReflectionClass($this->repository);
        $constructor = $reflection->getConstructor();
        
        if (!$constructor) {
            $this->assertEmpty($expectedDependencies, 'Repository should not have constructor dependencies');
            return;
        }

        $parameters = $constructor->getParameters();
        $this->assertCount(count($expectedDependencies), $parameters);

        foreach ($parameters as $index => $parameter) {
            $type = $parameter->getType();
            $expectedType = $expectedDependencies[$index];
            
            if ($type && !$type->isBuiltin()) {
                $this->assertEquals($expectedType, $type->getName());
            }
        }
    }

    /**
     * Assert that a repository returns expected type
     */
    protected function assertRepositoryReturns($result, string $expectedType): void
    {
        if (class_exists($expectedType) || interface_exists($expectedType)) {
            $this->assertInstanceOf($expectedType, $result);
        } else {
            $actualType = gettype($result);
            $this->assertEquals($expectedType, $actualType);
        }
    }

    /**
     * Assert that repository throws expected exception
     */
    protected function assertRepositoryThrowsException(callable $callback, string $expectedException): void
    {
        $this->expectException($expectedException);
        $callback();
    }

    /**
     * Assert that repository validates input correctly
     */
    protected function assertRepositoryValidatesInput(callable $callback, $invalidInput, string $expectedException): void
    {
        $this->expectException($expectedException);
        $callback($invalidInput);
    }

    /**
     * Assert that repository performs transaction
     */
    protected function assertRepositoryUsesTransaction(): void
    {
        $this->app['db']->shouldReceive('beginTransaction')->once();
        $this->app['db']->shouldReceive('commit')->once();
    }

    /**
     * Assert that repository rolls back on exception
     */
    protected function assertRepositoryRollsBackOnException(callable $callback, string $expectedException): void
    {
        $this->app['db']->shouldReceive('beginTransaction')->once();
        $this->app['db']->shouldReceive('rollBack')->once();
        
        $this->expectException($expectedException);
        $callback();
    }

    /**
     * Assert that repository method was called
     */
    protected function assertRepositoryMethodCalled($mock, string $method, array $parameters = []): void
    {
        if (empty($parameters)) {
            $mock->shouldHaveReceived($method)->once();
        } else {
            $mock->shouldHaveReceived($method)->once()->with(...$parameters);
        }
    }

    /**
     * Assert that repository method was not called
     */
    protected function assertRepositoryMethodNotCalled($mock, string $method): void
    {
        $mock->shouldNotHaveReceived($method);
    }

    /**
     * Assert pagination result structure
     */
    protected function assertValidPaginationResult($result): void
    {
        $this->assertInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class, $result);
    }

    /**
     * Assert UUID format
     */
    protected function assertValidRepositoryUuid(string $uuid): void
    {
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $uuid,
            'Invalid UUID format'
        );
    }

    /**
     * Assert query builder instance
     */
    protected function assertValidQueryBuilder($builder): void
    {
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $builder);
    }

    /**
     * Assert collection result
     */
    protected function assertValidCollectionResult($result): void
    {
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $result);
    }
}