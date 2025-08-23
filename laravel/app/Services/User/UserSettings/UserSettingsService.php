<?php

namespace App\Services\User\UserSettings;

use App\DTOs\User\NotificationPreferencesDTO;
use App\DTOs\User\PasswordChangeDTO;
use App\DTOs\User\UserPreferencesDTO;
use App\Models\User\User;
use App\Models\User\UserSetting;
use App\Repositories\User\UserSettings\UserSettingsRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class UserSettingsService
{
    public function __construct(protected UserSettingsRepositoryInterface $userSettingsRepository) {}

    public function getUserSettings(User $user): UserSetting
    {
        return $user->userSettings ?? $this->createDefaultSettings($user);
    }

    public function createDefaultSettings(User $user): UserSetting
    {
        $existingSettings = UserSetting::where('user_uuid', $user->uuid)->first();
        if ($existingSettings) {
            return $existingSettings;
        }

        $settings = $this->userSettingsRepository->createDefaultSettings($user->uuid);
        $user->load('userSettings');
        return $settings;
    }

    public function updateNotificationPreferences(User $user, NotificationPreferencesDTO $preferences): UserSetting
    {
        $settings = $this->getUserSettings($user);

        if (!$settings) {
            $settings = $this->createDefaultSettings($user);
        }

        $settings->update($preferences->toArray());
        return $settings->fresh();
    }

    public function updateUserPreferences(User $user, UserPreferencesDTO $preferences): User
    {
        $user->update($preferences->toArray());
        return $user->fresh();
    }

    public function changePassword(User $user, PasswordChangeDTO $passwordChange): bool
    {
        if (!Hash::check($passwordChange->currentPassword, $user->password)) {
            return false;
        }

        $user->update(['password' => $passwordChange->newPassword]);
        return true;
    }
}
