<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ApiExceptionHandler
{
    public static function handle(Throwable $e, Request $request): JsonResponse
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->error(
                errors: [],
                message: $e->getMessage() ?: 'An error occurred',
                statusCode: 500
            );
        }

        throw $e;
    }
}
