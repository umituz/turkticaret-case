<?php

namespace App\Repositories\User\UserSettings;

use App\Models\User\UserSetting;
use App\Repositories\Base\BaseRepository;

/**
 * User settings repository for handling user settings database operations.
 * 
 * This repository provides methods for managing user settings including
 * finding settings by user UUID and creating default settings.
 *
 * @package App\Repositories\User\UserSettings
 */
class UserSettingsRepository extends BaseRepository implements UserSettingsRepositoryInterface
{
    /**
     * Create a new UserSettings repository instance.
     *
     * @param UserSetting $model The UserSetting model instance
     */
    public function __construct(UserSetting $model)
    {
        parent::__construct($model);
    }

    /**
     * Find user settings by user UUID.
     *
     * @param string $userUuid The user UUID to search for
     * @return UserSetting|null The user settings instance or null if not found
     */
    public function findByUserUuid(string $userUuid): ?UserSetting
    {
        return $this->model->where('user_uuid', $userUuid)->first();
    }

    /**
     * Create default settings for a user or return existing settings.
     *
     * @param string $userUuid The user UUID to create settings for
     * @return UserSetting The created or existing user settings instance
     */
    public function createDefaultSettings(string $userUuid): UserSetting
    {
        $existingSettings = $this->findByUserUuid($userUuid);
        if ($existingSettings) {
            return $existingSettings;
        }

        return $this->model->create([
            'user_uuid' => $userUuid,
            'email_notifications' => true,
            'push_notifications' => true,
            'sms_notifications' => false,
            'marketing_notifications' => false,
            'order_update_notifications' => true,
            'newsletter_notifications' => false,
        ]);
    }

    /**
     * Update user settings by user UUID.
     *
     * @param string $userUuid The user UUID to update settings for
     * @param array $data The data to update
     * @return bool Whether the update was successful
     */
    public function updateByUserUuid(string $userUuid, array $data): bool
    {
        return $this->model->where('user_uuid', $userUuid)->update($data);
    }
}
