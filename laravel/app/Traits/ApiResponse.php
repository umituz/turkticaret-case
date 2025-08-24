<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * API Response trait for standardized HTTP responses.
 * 
 * Provides a consistent interface for generating API responses with proper
 * status codes, messages, and data formatting. Includes methods for success,
 * error, validation, and various HTTP status responses commonly used in APIs.
 *
 * @package App\Traits
 */
trait ApiResponse
{
    /**
     * Generate a successful response with HTTP 200 status.
     *
     * @param mixed $data Optional data to include in the response
     * @param string|null $message Optional custom success message
     * @return JsonResponse JSON response with success status and data
     */
    public function ok($data = null, $message = null): JsonResponse
    {
        $message = $message ?? __('Your execution has been completed successfully');

        if ($data instanceof \Illuminate\Http\Resources\Json\ResourceCollection) {
            return response()->success($data->toArray(request()), __($message), Response::HTTP_OK);
        }

        return response()->success($data, __($message), Response::HTTP_OK);
    }

    /**
     * Generate an error response with customizable status code.
     *
     * @param array $errors Array of error details or validation errors
     * @param string|null $message Optional custom error message
     * @param int $statusCode HTTP status code for the error response
     * @return JsonResponse JSON response with error status and details
     */
    public function error($errors = [], $message = null, $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR): JsonResponse
    {
        $message = $message ?? __('There is something wrong. Please, try again later!');

        return response()->error($errors, __($message), $statusCode);
    }

    /**
     * Generate a created response with HTTP 201 status.
     *
     * @param mixed $data Optional data representing the created resource
     * @param string|null $message Optional custom creation success message
     * @return JsonResponse JSON response indicating successful resource creation
     */
    public function created($data = null, $message = null): JsonResponse
    {
        $message = $message ?? __('Your execution has been completed successfully');

        return response()->success($data, __($message), Response::HTTP_CREATED);
    }

    /**
     * Generate a no content response with HTTP 204 status.
     *
     * @param array $data Optional empty data array for consistency
     * @param string|null $message Optional message (typically not shown in 204 responses)
     * @return JsonResponse JSON response indicating successful operation with no content
     */
    public function noContent($data = [], $message = null): JsonResponse
    {
        $message = $message ?? __('Your execution has been completed successfully');

        return response()->success($data, __($message), Response::HTTP_NO_CONTENT);
    }

    /**
     * Generate a validation error response with HTTP 422 status.
     *
     * @param array $errors Array of validation error messages
     * @param string $message Custom validation error message
     * @return JsonResponse JSON response with validation errors
     */
    public function validationWarning($errors = [], $message = ''): JsonResponse
    {
        return response()->error(
            errors: $errors,
            message: $message,
            statusCode: Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    /**
     * Generate a not found response with HTTP 404 status.
     *
     * @param string $message Custom not found message
     * @return JsonResponse JSON response indicating resource not found
     */
    public function notFound(string $message = 'Not Found'): JsonResponse
    {
        return response()->error([], __($message), Response::HTTP_NOT_FOUND);
    }

    /**
     * Generate an unauthorized response with HTTP 401 status.
     *
     * @param array $errors Optional array of authentication error details
     * @return JsonResponse JSON response indicating authentication failure
     */
    public function unauthorized($errors = []): JsonResponse
    {
        return response()->error(
            errors: $errors,
            statusCode: Response::HTTP_UNAUTHORIZED,
            message: __('Please, login and try again!')
        );
    }
}