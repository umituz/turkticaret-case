<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\Auth\RegisterResource;
use App\Services\Auth\RegisterService;
use Illuminate\Http\JsonResponse;

/**
 * REST API Controller for User Registration.
 * 
 * Handles new user registration with validation, account creation,
 * and automatic JWT token generation for immediate authentication.
 *
 * @package App\Http\Controllers\Auth
 */
class RegisterController extends BaseController
{
    /**
     * Create a new RegisterController instance.
     *
     * @param RegisterService $registerService The registration service for user account creation
     */
    public function __construct(protected RegisterService $registerService) {}

    /**
     * Register a new user account.
     *
     * @param RegisterRequest $request The validated registration request containing user details
     * @return JsonResponse JSON response containing created user data, access token, and token type with 201 status
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->registerService->register($request->validated());

        return $this->created([
            'user' => new RegisterResource($result['user']),
            'token' => $result['token'],
            'token_type' => 'Bearer',
        ], 'Registration successful.');
    }
}