<?php

namespace Tests\Base;

use Tests\Base\UnitTestCase;
use Tests\Traits\ServiceAssertionsTrait;
use Tests\Traits\ServiceMocksTrait;

/**
 * Base test case for service unit tests
 * Provides minimal helper methods for testing service business logic
 */
abstract class BaseServiceUnitTest extends UnitTestCase
{
    use ServiceAssertionsTrait, ServiceMocksTrait;

    /**
     * The service instance to test
     */
    protected $service;

    /**
     * Get the service class being tested
     */
    abstract protected function getServiceClass(): string;

    /**
     * Set up dependencies for the service
     * Override this method to inject dependencies
     */
    protected function getServiceDependencies(): array
    {
        return [];
    }

    /**
     * Set up the test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        $serviceClass = $this->getServiceClass();
        $dependencies = $this->getServiceDependencies();
        
        if (empty($dependencies)) {
            $this->service = new $serviceClass();
        } else {
            $this->service = new $serviceClass(...$dependencies);
        }
    }
}