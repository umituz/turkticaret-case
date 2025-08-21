<?php

namespace Tests\Traits;

use Illuminate\Database\Eloquent\Model;

/**
 * Trait providing common assertions for observer unit tests
 */
trait ObserverAssertionsTrait
{
    /**
     * Assert that observer has a specific method
     */
    protected function assertObserverHasMethod(string $method): void
    {
        $this->assertTrue(
            method_exists($this->observer, $method),
            "Observer does not have method: {$method}"
        );
    }

    /**
     * Assert that observer method was called
     */
    protected function assertObserverMethodCalled($mock, string $method, array $parameters = []): void
    {
        if (empty($parameters)) {
            $mock->shouldHaveReceived($method)->once();
        } else {
            $mock->shouldHaveReceived($method)->once()->with(...$parameters);
        }
    }

    /**
     * Assert that observer method was not called
     */
    protected function assertObserverMethodNotCalled($mock, string $method): void
    {
        $mock->shouldNotHaveReceived($method);
    }

    /**
     * Assert that observer extends expected base class
     */
    protected function assertExtendsObserverBase(string $expectedBaseClass): void
    {
        $this->assertInstanceOf(
            $expectedBaseClass,
            $this->observer,
            "Observer does not extend expected base class: {$expectedBaseClass}"
        );
    }

    /**
     * Assert that model attribute was set
     */
    protected function assertModelAttributeSet(Model $model, string $attribute, $expectedValue = null): void
    {
        if ($expectedValue !== null) {
            $this->assertEquals($expectedValue, $model->getAttribute($attribute));
        } else {
            $this->assertNotNull($model->getAttribute($attribute));
        }
    }

    /**
     * Assert that model UUID was generated
     */
    protected function assertModelUuidGenerated(Model $model): void
    {
        $this->assertNotNull($model->uuid);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $model->uuid
        );
    }

    /**
     * Assert that activity was logged
     */
    protected function assertActivityLogged($mock, Model $model, string $action, array $data = null): void
    {
        if ($data !== null) {
            $mock->shouldHaveReceived('logActivity')->once()->with($model, $action, $data);
        } else {
            $mock->shouldHaveReceived('logActivity')->once()->with($model, $action);
        }
    }
}