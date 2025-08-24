<?php

namespace App\Services\Setting;

use App\DTOs\Setting\SettingUpdateDTO;
use App\Enums\Setting\SettingKeyEnum;
use App\Repositories\Setting\SettingsRepositoryInterface;

/**
 * Settings Service for system configuration management.
 * 
 * Handles system-wide settings operations including retrieving active settings,
 * updating configuration values, and providing system status information.
 * Manages application-level configuration and preferences.
 *
 * @package App\Services\Setting
 */
class SettingsService
{
    /**
     * Create a new SettingsService instance.
     *
     * @param SettingsRepositoryInterface $settingsRepository The settings repository for data operations
     */
    public function __construct(protected SettingsRepositoryInterface $settingsRepository) {}

    /**
     * Get all active system settings as key-value pairs.
     *
     * @return \Illuminate\Support\Collection Collection of settings mapped as key => typed_value pairs
     */
    public function getAllActiveSettings()
    {
        return $this->settingsRepository->getAllActive()->mapWithKeys(function ($setting) {
            return [$setting->key => $setting->typed_value];
        });
    }

    /**
     * Update a specific system setting value.
     *
     * @param string $key The setting key to update
     * @param mixed $value The new value for the setting
     * @return bool True if update was successful, false if key is invalid
     */
    public function updateSetting(string $key, mixed $value): bool
    {
        $updateDTO = SettingUpdateDTO::fromRequest($key, $value);
        $settingKey = SettingKeyEnum::tryFrom($updateDTO->key);
        
        if (!$settingKey) {
            return false;
        }

        return $this->settingsRepository->updateByKey($settingKey, $updateDTO->value);
    }

    /**
     * Get system status information based on current settings.
     *
     * @return array Array containing system status flags and configuration states
     */
    public function getSystemStatus(): array
    {
        $settings = $this->getAllActiveSettings();
        
        return [
            'maintenance_mode' => $settings['maintenance_mode'] ?? false,
            'registration_enabled' => $settings['registration_enabled'] ?? true,
            'email_notifications' => $settings['email_notifications_enabled'] ?? true,
            'sms_notifications' => $settings['sms_notifications_enabled'] ?? false,
        ];
    }
}