<?php

namespace App\Services\Setting;

use App\DTOs\Setting\SettingUpdateDTO;
use App\Enums\Setting\SettingKeyEnum;
use App\Repositories\Setting\SettingsRepositoryInterface;

class SettingsService
{
    public function __construct(protected SettingsRepositoryInterface $settingsRepository) {}

    public function getAllActiveSettings()
    {
        return $this->settingsRepository->getAllActive()->mapWithKeys(function ($setting) {
            return [$setting->key => $setting->typed_value];
        });
    }

    public function updateSetting(string $key, mixed $value): bool
    {
        $updateDTO = SettingUpdateDTO::fromRequest($key, $value);
        $settingKey = SettingKeyEnum::tryFrom($updateDTO->key);
        
        if (!$settingKey) {
            return false;
        }

        return $this->settingsRepository->updateByKey($settingKey, $updateDTO->value);
    }

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