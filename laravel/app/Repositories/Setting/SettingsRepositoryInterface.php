<?php

namespace App\Repositories\Setting;

use App\Enums\Setting\SettingKeyEnum;
use App\Repositories\Base\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Contract for Settings repository implementations.
 * 
 * Defines the required methods for Settings data access layer operations
 * including application settings management, configuration retrieval,
 * and system preference handling functionality.
 *
 * @package App\Repositories\Setting
 */
interface SettingsRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get all active settings from the database.
     *
     * @return Collection<int, mixed> Collection of active settings
     */
    public function getAllActive(): Collection;

    /**
     * Update a setting value by its key.
     *
     * @param SettingKeyEnum $key The setting key to update
     * @param mixed $value The new value to set
     * @return bool True if update was successful, false otherwise
     */
    public function updateByKey(SettingKeyEnum $key, mixed $value): bool;
}