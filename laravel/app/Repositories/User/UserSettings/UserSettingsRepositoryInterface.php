<?php

namespace App\Repositories\User\UserSettings;

use App\Repositories\Base\BaseRepositoryInterface;

/**
 * Contract for User Settings repository implementations.
 * 
 * Defines the required methods for User Settings data access layer operations
 * including user-specific settings management, default settings creation,
 * and user preference handling.
 *
 * @package App\Repositories\User\UserSettings
 */
interface UserSettingsRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find user settings by user UUID.
     *
     * @param string $userUuid The unique identifier of the user
     * @return mixed The user settings instance if found, null otherwise
     */
    public function findByUserUuid(string $userUuid);
    
    /**
     * Create default settings for a new user.
     *
     * @param string $userUuid The unique identifier of the user
     * @return mixed The created default settings instance
     */
    public function createDefaultSettings(string $userUuid);

    /**
     * Update user settings with given data.
     *
     * @param string $userUuid The unique identifier of the user
     * @param array $data The data to update
     * @return bool Whether the update was successful
     */
    public function updateByUserUuid(string $userUuid, array $data): bool;
}
