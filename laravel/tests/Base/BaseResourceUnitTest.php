<?php

namespace Tests\Base;

use Tests\Base\UnitTestCase;
use Tests\Traits\ResourceAssertionsTrait;
use Tests\Traits\ResourceMocksTrait;
use Illuminate\Http\Request;

/**
 * Base test case for resource unit tests
 * Provides minimal helper methods for testing resource transformation logic
 */
abstract class BaseResourceUnitTest extends UnitTestCase
{
    use ResourceAssertionsTrait, ResourceMocksTrait;

    /**
     * The resource instance to test
     */
    protected $resource;

    /**
     * Mock request instance
     */
    protected Request $requestMock;

    /**
     * Get the resource class being tested
     */
    abstract protected function getResourceClass(): string;

    /**
     * Set up the test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->requestMock = $this->createMockRequest();
    }

    /**
     * Create a mock Request instance
     */
    protected function createMockRequest(): Request
    {
        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('expectsJson')->andReturn(true);
        $request->shouldReceive('wantsJson')->andReturn(true);
        $request->shouldReceive('path')->andReturn('/test');
        $request->shouldReceive('url')->andReturn('http://test.local/test');
        
        return $request;
    }
}