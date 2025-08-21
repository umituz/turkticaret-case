<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Throwable;

class ApiExceptionHandler
{
    /**
     * @throws Throwable
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
