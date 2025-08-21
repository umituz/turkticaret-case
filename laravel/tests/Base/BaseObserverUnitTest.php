<?php

namespace Tests\Base;

use Tests\Base\UnitTestCase;
use Tests\Traits\ObserverAssertionsTrait;
use Tests\Traits\ObserverMocksTrait;
use Mockery;

/**
 * Base test case for observer unit tests
 * Provides minimal helper functionality for observer testing without database connections
 */
abstract class BaseObserverUnitTest extends UnitTestCase
{
    use ObserverAssertionsTrait, ObserverMocksTrait;

    /**
     * The observer instance to test
     */
    protected $observer;

    /**
     * Get the observer class being tested
     */
    abstract protected function getObserverClass(): string;

    /**
     * Set up the test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        $observerClass = $this->getObserverClass();
        $this->observer = new $observerClass();
    }
}