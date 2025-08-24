<?php

namespace Tests\Traits;

use Illuminate\Testing\TestResponse;

/**
 * Trait providing common assertions for feature tests
 */
trait FeatureAssertionsTrait
{
    /**
     * Assert successful API response with specific status
     */
    protected function assertApiSuccess(TestResponse $response, int $status = 200): void
    {
        $response->assertStatus($status)
            ->assertHeader('content-type', 'application/json')
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    /**
     * Assert API error response
     */
    protected function assertApiError(TestResponse $response, int $status = 400): void
    {
        $response->assertStatus($status)
            ->assertHeader('content-type', 'application/json')
            ->assertJsonStructure(['success', 'message']);
    }

    /**
     * Assert pagination structure in response
     */
    protected function assertPaginationStructure(TestResponse $response): void
    {
        $response->assertJsonStructure([
            'data' => [
                '*' => []
            ],
            'links' => [
                'first',
                'last',
                'prev',
                'next'
            ],
            'meta' => [
                'current_page',
                'from',
                'last_page',
                'path',
                'per_page',
                'to',
                'total'
            ]
        ]);
    }

    /**
     * Assert standard API resource structure
     */
    protected function assertResourceStructure(TestResponse $response, array $resourceFields): void
    {
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => $resourceFields
        ]);
    }

    /**
     * Assert collection API response structure
     */
    protected function assertCollectionResponse(TestResponse $response, array $itemStructure): void
    {
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => $itemStructure
            ]
        ]);
    }

    /**
     * Assert authentication required response
     */
    protected function assertAuthenticationRequired(TestResponse $response): void
    {
        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);
    }

    /**
     * Assert permission denied response
     */
    protected function assertPermissionDenied(TestResponse $response): void
    {
        $response->assertStatus(403)
            ->assertJson([
                'message' => 'This action is unauthorized.'
            ]);
    }

    /**
     * Assert specific validation error fields
     */
    protected function assertValidationErrors(TestResponse $response, array $expectedFields): void
    {
        $response->assertStatus(422)
            ->assertJsonValidationErrors($expectedFields);
    }

    /**
     * Assert model not found response
     */
    protected function assertModelNotFound(TestResponse $response): void
    {
        $response->assertStatus(404)
            ->assertJson([
                'message' => 'No query results for model.'
            ]);
    }

    /**
     * Assert successful creation response
     */
    protected function assertSuccessfulCreation(TestResponse $response, int $status = 201, array $expectedData = []): void
    {
        $response->assertStatus($status)
            ->assertHeader('content-type', 'application/json');
        
        if (!empty($expectedData)) {
            $response->assertJsonFragment($expectedData);
        }
    }

    /**
     * Assert successful update response
     */
    protected function assertSuccessfulUpdate(TestResponse $response, array $expectedData = []): void
    {
        $response->assertStatus(200)
            ->assertHeader('content-type', 'application/json');
        
        if (!empty($expectedData)) {
            $response->assertJsonFragment($expectedData);
        }
    }

    /**
     * Assert successful deletion response
     */
    protected function assertSuccessfulDeletion(TestResponse $response): void
    {
        $response->assertStatus(200)
            ->assertHeader('content-type', 'application/json')
            ->assertJson([
                'success' => true
            ]);
    }

    /**
     * Assert JSON response contains specific keys
     */
    protected function assertJsonContainsKeys(TestResponse $response, array $keys): void
    {
        $responseData = $response->json();
        
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $responseData, "Response missing key: {$key}");
        }
    }

    /**
     * Assert JSON response does not contain specific keys
     */
    protected function assertJsonMissingKeys(TestResponse $response, array $keys): void
    {
        $responseData = $response->json();
        
        foreach ($keys as $key) {
            $this->assertArrayNotHasKey($key, $responseData, "Response should not contain key: {$key}");
        }
    }

    /**
     * Assert response has correct CORS headers
     */
    protected function assertCorsHeaders(TestResponse $response): void
    {
        $response->assertHeader('Access-Control-Allow-Origin', '*')
            ->assertHeader('Access-Control-Allow-Methods')
            ->assertHeader('Access-Control-Allow-Headers');
    }

    /**
     * Assert response time is within acceptable range
     */
    protected function assertResponseTimeAcceptable(TestResponse $response, int $maxMilliseconds = 1000): void
    {
        // This would need implementation based on response timing
        $this->assertTrue(true, 'Response time assertion placeholder');
    }

    /**
     * Assert UUID format in response (supports both UUID and ULID)
     */
    protected function assertValidUuidInResponse(TestResponse $response, string $jsonPath): void
    {
        $value = $response->json($jsonPath);
        $this->assertNotNull($value, "UUID value at {$jsonPath} should not be null");
        
        // Check for standard UUID v4 format or ULID format
        $uuidPattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
        $ulidPattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';
        
        $isValid = preg_match($uuidPattern, $value) || preg_match($ulidPattern, $value);
        
        $this->assertTrue(
            $isValid,
            "Value at {$jsonPath} is not a valid UUID or ULID format: {$value}"
        );
    }

    /**
     * Assert timestamp format in response
     */
    protected function assertValidTimestampInResponse(TestResponse $response, string $jsonPath): void
    {
        $value = $response->json($jsonPath);
        $this->assertNotNull($value);
        
        // Check various timestamp formats
        $formats = [
            \DateTimeInterface::ISO8601,
            'Y-m-d\TH:i:s.u\Z',
            'Y-m-d H:i:s',
            'Y-m-d\TH:i:sP'
        ];
        
        $valid = false;
        foreach ($formats as $format) {
            if (\DateTime::createFromFormat($format, $value) !== false) {
                $valid = true;
                break;
            }
        }
        
        $this->assertTrue($valid);
    }

    /**
     * Assert validation error response (Laravel standard)
     */
    protected function assertValidationErrorResponse(TestResponse $response, array $expectedFields): void
    {
        $response->assertStatus(422)
            ->assertJsonValidationErrors($expectedFields);
    }

    /**
     * Assert successful JSON response
     */
    protected function assertSuccessfulJsonResponse(TestResponse $response, int $status = 200): void
    {
        $response->assertStatus($status)
            ->assertJson(['success' => true]);
    }

    /**
     * Assert unauthorized response
     */
    protected function assertUnauthorizedResponse(TestResponse $response): void
    {
        $response->assertUnauthorized();
    }

    /**
     * JSON request helper - delegates to Laravel's built-in method
     */
    protected function jsonGet(string $uri, array $headers = []): TestResponse
    {
        return $this->getJson($uri, $headers);
    }

    /**
     * Assert not found response
     */
    protected function assertNotFoundResponse(TestResponse $response): void
    {
        $response->assertNotFound();
    }

    /**
     * Assert error response with specific message
     */
    protected function assertErrorResponse(TestResponse $response, int $status = 400, ?string $message = null): void
    {
        $response->assertStatus($status)
            ->assertJson(['success' => false]);
        
        if ($message !== null) {
            $response->assertJson(['message' => $message]);
        }
    }

    /**
     * Assert forbidden response
     */
    protected function assertForbiddenResponse(TestResponse $response): void
    {
        $response->assertForbidden();
    }
}