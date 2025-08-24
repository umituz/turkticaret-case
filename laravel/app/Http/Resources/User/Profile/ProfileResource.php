<?php

namespace App\Http\Resources\User\Profile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource for transforming User Profile data.
 * 
 * Handles the transformation of User model instances into standardized
 * JSON API responses for profile management. Includes secure user data
 * representation with verification status and timestamps.
 *
 * @package App\Http\Resources\User\Profile
 */
class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request The HTTP request instance
     * @return array<string, mixed> Array representation of the user profile resource
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
