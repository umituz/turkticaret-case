<?php

namespace App\Exceptions;

use App\Exceptions\Product\InsufficientStockException;
use App\Exceptions\Product\OutOfStockException;
use App\Exceptions\Order\EmptyCartException;
use App\Exceptions\Order\MinimumOrderAmountException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Throwable;

/**
 * API Exception Handler for centralized exception processing.
 * 
 * Provides unified exception handling for API requests with standardized
 * JSON error responses. Maps various exception types to appropriate HTTP
 * status codes and error messages for consistent API error handling.
 *
 * @package App\Exceptions
 */
class ApiExceptionHandler
{
    /**
     * Handle exceptions for API requests with standardized JSON responses.
     * 
     * Converts various exception types into consistent JSON error responses
     * with appropriate HTTP status codes. Only processes API requests or
     * requests expecting JSON responses.
     *
     * @param Throwable $e The exception to handle
     * @param Request $request The HTTP request instance
     * @return JsonResponse Standardized JSON error response
     * @throws Throwable Re-throws non-API exceptions
     */
    public static function handle(Throwable $e, Request $request): JsonResponse
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            return match (true) {
                $e instanceof ValidationException => response()->error(
                    errors: $e->errors(),
                    message: $e->getMessage(),
                    statusCode: 422
                ),
                $e instanceof AuthenticationException => response()->error(
                    errors: [],
                    message: 'Unauthenticated.',
                    statusCode: 401
                ),
                $e instanceof ModelNotFoundException => response()->error(
                    errors: [],
                    message: 'No query results for model.',
                    statusCode: 404
                ),
                $e instanceof NotFoundHttpException => response()->error(
                    errors: [],
                    message: 'Not found.',
                    statusCode: 404
                ),
                $e instanceof AccessDeniedHttpException => response()->error(
                    errors: [],
                    message: 'This action is unauthorized.',
                    statusCode: 403
                ),
                $e instanceof UnauthorizedException => response()->error(
                    errors: [],
                    message: 'User does not have the right roles.',
                    statusCode: 403
                ),
                $e instanceof OutOfStockException, $e instanceof InsufficientStockException, $e instanceof EmptyCartException, $e instanceof MinimumOrderAmountException => response()->error(
                    errors: [],
                    message: $e->getMessage(),
                    statusCode: 422
                ),
                $e instanceof \InvalidArgumentException => response()->error(
                    errors: [],
                    message: $e->getMessage(),
                    statusCode: 422
                ),
                default => response()->error(
                    errors: [],
                    message: $e->getMessage() ?: 'An error occurred',
                    statusCode: 500
                )
            };
        }

        throw $e;
    }
}
