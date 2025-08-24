<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource for transforming User Login data.
 * 
 * Handles the transformation of authenticated User model instances into
 * standardized JSON API responses. Includes user profile data, role information,
 * and authentication context for successful login responses.
 *
 * @package App\Http\Resources\Auth
 */
class LoginResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request The HTTP request instance
     * @return array<string, mixed> Array representation of the authenticated user resource
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->hasRole('Admin') ? 'admin' : 'user',
            'roles' => $this->roles->pluck('name'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}