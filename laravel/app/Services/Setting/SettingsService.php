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
}