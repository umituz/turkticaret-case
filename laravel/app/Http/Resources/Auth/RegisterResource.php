<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource for transforming User Registration data.
 * 
 * Handles the transformation of newly registered User model instances into
 * standardized JSON API responses. Provides secure user data representation
 * without sensitive information for registration confirmations.
 *
 * @package App\Http\Resources\Auth
 */
class RegisterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request The HTTP request instance
     * @return array<string, mixed> Array representation of the registered user resource
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}