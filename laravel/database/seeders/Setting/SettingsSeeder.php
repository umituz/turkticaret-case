<?php

namespace Database\Seeders\Setting;

use App\Enums\Setting\SettingKeyEnum;
use App\Models\Setting\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (SettingKeyEnum::cases() as $settingKey) {
            Setting::firstOrCreate(
                ['key' => $settingKey->value],
                [
                    'value' => $settingKey->getDefaultValue(),
                    'type' => $settingKey->getType(),
                    'group' => $settingKey->getGroup(),
                    'description' => $settingKey->getDescription(),
                    'is_active' => true,
                    'is_editable' => true,
                ]
            );
        }
    }
}
