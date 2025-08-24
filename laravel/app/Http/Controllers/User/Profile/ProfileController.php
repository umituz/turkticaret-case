<?php

namespace App\Http\Controllers\User\Profile;

use App\Http\Controllers\BaseController;
use App\Http\Requests\User\Profile\ProfileUpdateRequest;
use App\Http\Resources\User\Profile\ProfileResource;
use App\Services\User\Profile\ProfileService;
use Illuminate\Http\JsonResponse;

/**
 * REST API Controller for User Profile management.
 * 
 * Handles user profile operations including viewing profile data,
 * updating profile information, and retrieving user statistics.
 * All operations are scoped to the authenticated user.
 *
 * @package App\Http\Controllers\User\Profile
 */
class ProfileController extends BaseController
{
    /**
     * Create a new ProfileController instance.
     *
     * @param ProfileService $profileService The profile service for user profile operations
     */
    public function __construct(protected ProfileService $profileService) {}

    /**
     * Display the authenticated user's profile.
     *
     * @return JsonResponse JSON response containing the user's profile resource
     */
    public function show(): JsonResponse
    {
        $user = auth()->user();
        $profile = $this->profileService->getProfile($user);

        return $this->ok(new ProfileResource($profile), 'Profile retrieved successfully.');
    }

    /**
     * Get statistics for the authenticated user.
     *
     * @return JsonResponse JSON response containing user statistics and metrics
     */
    public function stats(): JsonResponse
    {
        $user = auth()->user();
        $stats = $this->profileService->getUserStats($user);

        return $this->ok($stats, 'User statistics retrieved successfully.');
    }

    /**
     * Update the authenticated user's profile.
     *
     * @param ProfileUpdateRequest $request The validated request containing profile update data
     * @return JsonResponse JSON response containing the updated profile resource
     */
    public function update(ProfileUpdateRequest $request): JsonResponse
    {
        $user = auth()->user();
        $updatedUser = $this->profileService->updateProfile($user, $request->validated());

        return $this->ok(new ProfileResource($updatedUser), 'Profile updated successfully.');
    }
}
