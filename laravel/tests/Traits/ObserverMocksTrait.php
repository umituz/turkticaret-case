<?php

namespace Tests\Traits;

use Illuminate\Database\Eloquent\Model;
use Mockery;

/**
 * Observer Mocks Trait for Unit Tests
 */
trait ObserverMocksTrait
{
    /**
     * Create a mock model for testing
     */
    protected function createMockModel(string $modelClass, array $attributes = []): Model
    {
        $model = Mockery::mock($modelClass)->makePartial();
        
        foreach ($attributes as $key => $value) {
            $model->shouldReceive('getAttribute')
                ->with($key)
                ->andReturn($value);
            $model->$key = $value;
        }

        $model->shouldReceive('getChanges')->andReturn($attributes);
        $model->shouldReceive('setAttribute')->andReturnSelf();
        
        return $model;
    }

    /**
     * Create a partial mock of the observer
     */
    protected function createObserverPartialMock(array $methods = []): object
    {
        $observerClass = $this->getObserverClass();
        $mock = Mockery::mock($observerClass)->makePartial();
        
        foreach ($methods as $method => $return) {
            if (is_string($method)) {
                $mock->shouldReceive($method)->andReturn($return);
            } else {
                $mock->shouldReceive($return)->andReturnSelf();
            }
        }
        
        return $mock;
    }

    /**
     * Create a spy of the observer
     */
    protected function createObserverSpy(): object
    {
        $observerClass = $this->getObserverClass();
        return Mockery::spy($observerClass);
    }

    /**
     * Mock the activity logging method
     */
    protected function mockActivityLogging($observer): void
    {
        $observer->shouldReceive('logActivity')->andReturn(true);
    }
}