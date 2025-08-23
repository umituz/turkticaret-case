<?php

namespace App\Repositories\User\UserSettings;

use App\Models\User\UserSetting;
use App\Repositories\BaseRepositoryInterface;

interface UserSettingsRepositoryInterface extends BaseRepositoryInterface
{
    public function findByUserUuid(string $userUuid): ?UserSetting;
    public function createDefaultSettings(string $userUuid): UserSetting;
}
