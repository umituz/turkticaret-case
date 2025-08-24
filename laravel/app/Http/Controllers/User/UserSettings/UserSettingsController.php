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

 /**
 * REST API Controller for User Settings management.
 *
 * Handles comprehensive user settings operations including notification preferences,
 * user preferences (language, timezone), password changes, and settings initialization.
 * All operations are scoped to the authenticated user and use DTOs for data transfer.
 *
 * @package App\Http\Controllers\User\UserSettings
 */
class UserSettingsController extends BaseController
{
    /**
     * Create a new UserSettingsController instance.
     *
     * @param UserSettingsService $userSettingsService The user settings service for settings operations
     */
    public function __construct(protected UserSettingsService $userSettingsService) {}

    /**
     * Retrieve the authenticated user's settings.
     *
     * @return JsonResponse JSON response containing user settings with loaded relationships
     */
    public function getUserSettings(): JsonResponse
    {
        $settings = $this->userSettingsService->getUserSettings(auth()->user());
        $settings->load('user:uuid,language_uuid,timezone');

        return $this->ok(new UserSettingsResource($settings), 'Settings retrieved successfully.');
    }

    /**
     * Create default settings for the authenticated user.
     *
     * @return JsonResponse JSON response containing newly created default settings
     */
    public function createDefaultSettings(): JsonResponse
    {
        $user = auth()->user();
        $settings = $this->userSettingsService->createDefaultSettings($user);
        $settings->load('user:uuid,language_uuid,timezone');

        return $this->ok(new UserSettingsResource($settings), 'Default settings created successfully.');
    }

    /**
     * Update notification preferences for the authenticated user.
     *
     * @param UserSettingsNotificationUpdateRequest $request The validated request containing notification preferences
     * @return JsonResponse JSON response containing updated user settings
     */
    public function updateNotifications(UserSettingsNotificationUpdateRequest $request): JsonResponse
    {
        $user = auth()->user();
        $notificationPreferences = NotificationPreferencesDTO::fromArray($request->validated());
        $settings = $this->userSettingsService->updateNotificationPreferences($user, $notificationPreferences);

        return $this->ok(new UserSettingsResource($settings), 'Notification preferences updated successfully.');
    }

    /**
     * Update user preferences including language and timezone.
     *
     * @param UserSettingsPreferencesUpdateRequest $request The validated request containing user preferences
     * @return JsonResponse JSON response containing updated language and timezone preferences
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
     * Change the authenticated user's password.
     *
     * @param UserSettingsPasswordChangeRequest $request The validated request containing current and new passwords
     * @return JsonResponse JSON response confirming password change success or validation error
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
