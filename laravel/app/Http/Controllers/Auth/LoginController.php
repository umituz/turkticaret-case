<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Auth\LoginResource;
use App\Services\Auth\LoginService;
use Illuminate\Http\JsonResponse;

class LoginController extends BaseController
{
    public function __construct(protected LoginService $loginService) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->loginService->login($request->validated());

        return $this->ok([
            'user' => new LoginResource($result['user']),
            'token' => $result['token'],
            'token_type' => 'Bearer',
        ], 'Login successful.');
    }
}