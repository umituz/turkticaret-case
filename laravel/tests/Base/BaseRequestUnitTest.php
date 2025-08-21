<?php

namespace Tests\Base;

use Tests\Base\UnitTestCase;
use Tests\Traits\RequestAssertionsTrait;
use Tests\Traits\RequestMocksTrait;

/**
 * Base test case for request unit tests
 * Provides minimal helper methods for testing request validation logic
 */
abstract class BaseRequestUnitTest extends UnitTestCase
{
    use RequestAssertionsTrait, RequestMocksTrait;

    /**
     * The request instance to test
     */
    protected $request;

    /**
     * Get the request class being tested
     */
    abstract protected function getRequestClass(): string;

    /**
     * Set up the test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        $requestClass = $this->getRequestClass();
        $this->request = new $requestClass();
    }

    /**
     * Get valid data for the request
     */
    abstract protected function getValidData(): array;

    /**
     * Get invalid data for the request with expected error fields
     */
    abstract protected function getInvalidDataCases(): array;
}