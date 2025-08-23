<?php

namespace App\Http\Controllers\User\UserSettings;

use App\DTOs\User\NotificationPreferencesDTO;
use App\DTOs\User\PasswordChangeDTO;
use App\DTOs\User\UserPreferencesDTO;
use App\Http\Controllers\BaseController;
use App\Http\Requests\User\UserSettings\UserSettingsNotificationUpdateRequest;
use App\Http\Requests\User\UserSettings\UserSettingsPasswordChangeRequest;
use App\Http\Requests\User\UserSettings\UserSettingsPreferencesUpdateRequest;
use App\Http\Resources\User\UserSettings\UserSettingsResource;
use App\Services\User\UserSettings\UserSettingsService;
use Illuminate\Http\JsonResponse;

class UserSettingsController extends BaseController
{
    public function __construct(protected UserSettingsService $userSettingsService) {}

    /**
     * Get user settings
     */
    public function getUserSettings(): JsonResponse
    {
        $settings = $this->userSettingsService->getUserSettings(auth()->user());
        $settings->load('user:uuid,language_uuid,timezone');

        return $this->ok(new UserSettingsResource($settings), 'Settings retrieved successfully.');
    }

    /**
     * Create default user settings
     */
    public function createDefaultSettings(): JsonResponse
    {
        $user = auth()->user();
        $settings = $this->userSettingsService->createDefaultSettings($user);
        $settings->load('user:uuid,language_uuid,timezone');

        return $this->ok(new UserSettingsResource($settings), 'Default settings created successfully.');
    }

    /**
     * Update notification preferences
     */
    public function updateNotifications(UserSettingsNotificationUpdateRequest $request): JsonResponse
    {
        $user = auth()->user();
        $notificationPreferences = NotificationPreferencesDTO::fromArray($request->validated());
        $settings = $this->userSettingsService->updateNotificationPreferences($user, $notificationPreferences);

        return $this->ok(new UserSettingsResource($settings), 'Notification preferences updated successfully.');
    }

    /**
     * Update user preferences
     */
    public function updatePreferences(UserSettingsPreferencesUpdateRequest $request): JsonResponse
    {
        $user = auth()->user();
        $userPreferences = UserPreferencesDTO::fromArray($request->validated());
        $updatedUser = $this->userSettingsService->updateUserPreferences($user, $userPreferences);

        return $this->ok([
            'language_uuid' => $updatedUser->language_uuid,
            'timezone' => $updatedUser->timezone,
        ], 'Preferences updated successfully.');
    }

    /**
     * Change user password
     */
    public function changePassword(UserSettingsPasswordChangeRequest $request): JsonResponse
    {
        $user = auth()->user();
        $passwordChange = PasswordChangeDTO::fromArray($request->validated());
        $success = $this->userSettingsService->changePassword($user, $passwordChange);

        if (!$success) {
            return $this->error(
                ['current_password' => ['Current password is incorrect.']],
                'Current password is incorrect.',
                422
            );
        }

        return $this->ok(null, 'Password changed successfully.');
    }
}
