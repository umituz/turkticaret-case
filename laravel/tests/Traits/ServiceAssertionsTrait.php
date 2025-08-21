<?php

namespace Tests\Traits;

use Mockery;

/**
 * Trait providing common assertions for service unit tests
 */
trait ServiceAssertionsTrait
{
    /**
     * Assert that service has a specific method
     */
    protected function assertServiceHasMethod(string $method): void
    {
        $this->assertTrue(
            method_exists($this->service, $method),
            "Service does not have method: {$method}"
        );
    }

    /**
     * Assert that service method was called
     */
    protected function assertServiceMethodCalled($mock, string $method, array $parameters = []): void
    {
        if (empty($parameters)) {
            $mock->shouldHaveReceived($method)->once();
        } else {
            $mock->shouldHaveReceived($method)->once()->with(...$parameters);
        }
    }

    /**
     * Assert that service method was not called
     */
    protected function assertServiceMethodNotCalled($mock, string $method): void
    {
        $mock->shouldNotHaveReceived($method);
    }

    /**
     * Assert that service has expected constructor dependencies
     */
    protected function assertHasConstructorDependencies(array $expectedDependencies): void
    {
        $reflection = new \ReflectionClass($this->service);
        $constructor = $reflection->getConstructor();
        
        if (!$constructor) {
            $this->assertEmpty($expectedDependencies, 'Service should not have constructor dependencies');
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
     * Assert that a service returns expected type
     */
    protected function assertServiceReturns($result, string $expectedType): void
    {
        if (class_exists($expectedType) || interface_exists($expectedType)) {
            $this->assertInstanceOf($expectedType, $result);
        } else {
            $actualType = gettype($result);
            $this->assertEquals($expectedType, $actualType);
        }
    }

    /**
     * Assert that service throws expected exception
     */
    protected function assertServiceThrowsException(callable $callback, string $expectedException): void
    {
        $this->expectException($expectedException);
        $callback();
    }

    /**
     * Assert that service validates input correctly
     */
    protected function assertServiceValidatesInput(callable $callback, $invalidInput, string $expectedException): void
    {
        $this->expectException($expectedException);
        $callback($invalidInput);
    }

    /**
     * Assert that service interacts with repository
     */
    protected function assertServiceUsesRepository($repositoryMock, string $method, array $parameters = []): void
    {
        if (empty($parameters)) {
            $repositoryMock->shouldReceive($method)->once()->andReturn(null);
        } else {
            $repositoryMock->shouldReceive($method)->once()->with(...$parameters)->andReturn(null);
        }
    }
}