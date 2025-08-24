<?php

namespace App\Repositories\Setting;

use App\Enums\Setting\SettingKeyEnum;
use App\Models\Setting\Setting;
use App\Repositories\Base\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * Settings Repository for application configuration data operations.
 * 
 * Handles system settings and configuration data including active settings
 * retrieval, setting updates by key, and configuration management.
 * Extends BaseRepository to provide settings-specific functionality.
 *
 * @package App\Repositories\Setting
 */
class SettingsRepository extends BaseRepository implements SettingsRepositoryInterface
{
    /**
     * Create a new SettingsRepository instance.
     *
     * @param Setting $model The Setting model instance for this repository
     */
    public function __construct(Setting $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all active settings.
     *
     * @return Collection Collection of active settings
     */
    public function getAllActive(): Collection
    {
        return $this->model->active()->get();
    }

    /**
     * Update a setting value by its key.
     *
     * @param SettingKeyEnum $key The setting key enum to update
     * @param mixed $value The new value for the setting
     * @return bool True if the setting was updated, false otherwise
     */
    public function updateByKey(SettingKeyEnum $key, mixed $value): bool
    {
        return $this->model->byKey($key->value)
            ->where('is_editable', true)
            ->update(['value' => $value]) > 0;
    }
}