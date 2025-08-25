<?php

namespace App\Services\User\UserSettings;

use App\DTOs\User\NotificationPreferencesDTO;
use App\DTOs\User\PasswordChangeDTO;
use App\DTOs\User\UserPreferencesDTO;
use App\Models\User\User;
use App\Models\User\UserSetting;
use App\Repositories\User\UserSettings\UserSettingsRepositoryInterface;
use Illuminate\Support\Facades\Hash;

/**
 * UserSettingsService manages user preferences, notification settings,
 * and account security features. This service handles the complete
 * user settings lifecycle including creation, updates, and password management.
 * 
 * @package App\Services\User\UserSettings
 */
class UserSettingsService
{
    /**
     * UserSettingsService constructor.
     * 
     * @param UserSettingsRepositoryInterface $userSettingsRepository Repository for user settings operations
     */
    public function __construct(protected UserSettingsRepositoryInterface $userSettingsRepository) {}

    /**
     * Retrieve user settings for the given user.
     * If settings don't exist, automatically creates default settings
     * to ensure every user has a complete settings profile.
     * 
     * @param User $user The user to retrieve settings for
     * @return UserSetting The user settings model (existing or newly created)
     */
    public function getUserSettings(User $user): UserSetting
    {
        return $user->userSettings ?? $this->createDefaultSettings($user);
    }

    /**
     * Create default settings for a user if they don't already exist.
     * Prevents duplicate settings creation by checking for existing records
     * before creating new default settings through the repository.
     * 
     * @param User $user The user to create default settings for
     * @return UserSetting The created or existing user settings model
     */
    public function createDefaultSettings(User $user): UserSetting
    {
        $existingSettings = $this->userSettingsRepository->findByUserUuid($user->uuid);
        if ($existingSettings) {
            return $existingSettings;
        }

        $settings = $this->userSettingsRepository->createDefaultSettings($user->uuid);
        $user->load('userSettings');
        return $settings;
    }

    /**
     * Create default settings with loaded relationships.
     *
     * @param User $user The user to create default settings for
     * @return UserSetting The created settings with loaded relationships
     */
    public function createDefaultSettingsWithRelations(User $user): UserSetting
    {
        $settings = $this->createDefaultSettings($user);
        return $settings->load('user:uuid,language_uuid,timezone');
    }

    /**
     * Update user notification preferences with validated data.
     * Ensures user has settings record before updating notification
     * preferences and returns fresh model data after update.
     * 
     * @param User $user The user whose notification preferences to update
     * @param NotificationPreferencesDTO $preferences Validated notification preferences data
     * @return UserSetting The updated user settings with fresh data
     */
    public function updateNotificationPreferences(User $user, NotificationPreferencesDTO $preferences): UserSetting
    {
        $settings = $this->getUserSettings($user);

        if (!$settings) {
            $settings = $this->createDefaultSettings($user);
        }

        $this->userSettingsRepository->updateByUserUuid($user->uuid, $preferences->toArray());
        return $this->userSettingsRepository->findByUserUuid($user->uuid);
    }

    /**
     * Update general user preferences such as language and display settings.
     * Updates user model directly with preference data and returns
     * fresh model instance with updated values.
     * 
     * @param User $user The user to update preferences for
     * @param UserPreferencesDTO $preferences Validated user preferences data
     * @return User The updated user model with fresh data
     */
    public function updateUserPreferences(User $user, UserPreferencesDTO $preferences): User
    {
        $user->update($preferences->toArray());
        return $user->fresh();
    }

    /**
     * Change user password after validating current password.
     * Verifies the current password before updating to new password
     * for security purposes. Returns success/failure status.
     * 
     * @param User $user The user whose password to change
     * @param PasswordChangeDTO $passwordChange Validated password change data with current and new passwords
     * @return bool True if password was changed successfully, false if current password is invalid
     */
    public function changePassword(User $user, PasswordChangeDTO $passwordChange): bool
    {
        if (!Hash::check($passwordChange->currentPassword, $user->password)) {
            return false;
        }

        $user->update(['password' => $passwordChange->newPassword]);
        return true;
    }

    /**
     * Get user settings with loaded relationships.
     *
     * @param User $user The user to get settings for
     * @return UserSetting The user settings with loaded relationships
     */
    public function getUserSettingsWithRelations(User $user): UserSetting
    {
        $settings = $this->getUserSettings($user);
        return $settings->load('user:uuid,language_uuid,timezone');
    }
}
