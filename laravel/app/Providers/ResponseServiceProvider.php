<?php

namespace App\Providers;

use Illuminate\Routing\ResponseFactory;
use Illuminate\Support\ServiceProvider;

class ResponseServiceProvider extends ServiceProvider
{
    public function boot(ResponseFactory $factory)
    {
        $factory->macro('success', function ($data = null, $message = '', $statusCode = null, array $headers = []) use ($factory) {
            $response = [
                'success' => true,
                'message' => $message,
                'data' => $data,
                'errors' => []
            ];

            return $factory->json($response, $statusCode ?? 200, $headers);
        });

        $factory->macro('error', function ($errors = [], $message = '', $statusCode = null, array $headers = []) use ($factory) {
            $response = [
                'success' => false,
                'message' => $message,
                'data' => null,
                'errors' => is_string($errors) ? ['error' => $errors] : $errors,
            ];

            return $factory->json($response, $statusCode ?? 400, $headers);
        });
    }
}