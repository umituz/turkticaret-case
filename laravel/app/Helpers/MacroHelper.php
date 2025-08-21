<?php

namespace App\Helpers;

use Closure;
use Illuminate\Routing\ResponseFactory;

class MacroHelper
{
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