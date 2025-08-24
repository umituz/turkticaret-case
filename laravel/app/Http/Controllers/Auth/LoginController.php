<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Auth\LoginResource;
use App\Services\Auth\LoginService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * REST API Controller for User Authentication.
 *
 * Handles user login and logout operations with JWT token generation
 * and management. Provides secure authentication endpoints for the API.
 *
 * @package App\Http\Controllers\Auth
 */
class LoginController extends BaseController
{
    /**
     * Create a new LoginController instance.
     *
     * @param LoginService $loginService The login service for authentication operations
     */
    public function __construct(protected LoginService $loginService) {}

    /**
     * Authenticate user and generate access token.
     *
     * @param LoginRequest $request The validated login request containing email and password
     * @return JsonResponse JSON response containing user data, access token, and token type
     * @throws ValidationException
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->loginService->login($request->validated());

        return $this->ok([
            'user' => new LoginResource($result['user']),
            'token' => $result['token'],
            'token_type' => 'Bearer',
        ], 'Login successful.');
    }

    /**
     * Logout the authenticated user and revoke access token.
     *
     * @param Request $request The HTTP request containing the authenticated user
     * @return JsonResponse JSON response confirming successful logout
     */
    public function logout(Request $request): JsonResponse
    {
        $this->loginService->logout($request->user());

        return $this->ok([], 'Logout successful.');
    }
}
