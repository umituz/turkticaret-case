<?php

namespace App\Http\Controllers\User\Profile;

use App\Http\Controllers\BaseController;
use App\Http\Requests\User\Profile\ProfileUpdateRequest;
use App\Http\Resources\User\Profile\ProfileResource;
use App\Services\User\Profile\ProfileService;
use Illuminate\Http\JsonResponse;

class ProfileController extends BaseController
{
    public function __construct(protected ProfileService $profileService) {}

    public function show(): JsonResponse
    {
        $user = auth()->user();
        $profile = $this->profileService->getProfile($user);

        return $this->ok(new ProfileResource($profile), 'Profile retrieved successfully.');
    }

    public function update(ProfileUpdateRequest $request): JsonResponse
    {
        $user = auth()->user();
        $updatedUser = $this->profileService->updateProfile($user, $request->validated());

        return $this->ok(new ProfileResource($updatedUser), 'Profile updated successfully.');
    }
}
