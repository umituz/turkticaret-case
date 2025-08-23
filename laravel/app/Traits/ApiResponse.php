<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

trait ApiResponse
{
    public function ok($data = null, $message = null): JsonResponse
    {
        $message = $message ?? __('Your execution has been completed successfully');

        if ($data instanceof \Illuminate\Http\Resources\Json\ResourceCollection) {
            return response()->success($data->toArray(request()), __($message), Response::HTTP_OK);
        }

        return response()->success($data, __($message), Response::HTTP_OK);
    }

    public function error($errors = [], $message = null, $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR): JsonResponse
    {
        $message = $message ?? __('There is something wrong. Please, try again later!');

        return response()->error($errors, __($message), $statusCode);
    }

    public function created($data = null, $message = null): JsonResponse
    {
        $message = $message ?? __('Your execution has been completed successfully');

        return response()->success($data, __($message), Response::HTTP_CREATED);
    }

    public function noContent($data = [], $message = null): JsonResponse
    {
        $message = $message ?? __('Your execution has been completed successfully');

        return response()->success($data, __($message), Response::HTTP_NO_CONTENT);
    }

    public function validationWarning($errors = [], $message = ''): JsonResponse
    {
        return response()->error(
            errors: $errors,
            message: $message,
            statusCode: Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    public function notFound(string $message = 'Not Found'): JsonResponse
    {
        return response()->error([], __($message), Response::HTTP_NOT_FOUND);
    }

    public function unauthorized($errors = []): JsonResponse
    {
        return response()->error(
            errors: $errors,
            statusCode: Response::HTTP_UNAUTHORIZED,
            message: __('Please, login and try again!')
        );
    }
}