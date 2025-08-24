<?php

namespace App\Helpers;

use Closure;
use Illuminate\Routing\ResponseFactory;

/**
 * Helper class for creating response macros.
 * 
 * This class provides factory methods for creating consistent API response
 * macros that can be registered with Laravel's ResponseFactory.
 * Ensures uniform response structure across the application.
 *
 * @package App\Helpers
 */
class MacroHelper
{
    /**
     * Create a success response macro closure.
     * 
     * Returns a closure that can be used as a macro to generate
     * consistent success responses with optional data and pagination.
     *
     * @param ResponseFactory $factory The response factory instance
     * @return Closure Success response macro closure
     */
    public static function success(ResponseFactory $factory): Closure
    {
        return function ($data = null, $message = '', $statusCode = null, array $headers = []) use ($factory) {
            $baseResponse = [
                'success' => true,
                'message' => $message,
                'errors' => []
            ];

            if (is_array($data) && isset($data['data']) && isset($data['meta'])) {
                $response = array_merge($baseResponse, $data);
            } else {
                $response = array_merge($baseResponse, ['data' => $data]);
            }

            return $factory->json($response, $statusCode ?? 200, $headers);
        };
    }

    /**
     * Create an error response macro closure.
     * 
     * Returns a closure that can be used as a macro to generate
     * consistent error responses with error details and messages.
     *
     * @param ResponseFactory $factory The response factory instance
     * @return Closure Error response macro closure
     */
    public static function error(ResponseFactory $factory): Closure
    {
        return function ($errors = [], $message = '', $statusCode = null, array $headers = []) use ($factory) {
            $response = [
                'success' => false,
                'message' => $message,
                'data' => null,
                'errors' => is_string($errors) ? ['error' => $errors] : $errors,
            ];

            return $factory->json($response, $statusCode ?? 400, $headers);
        };
    }
}