<?php

namespace App\Repositories\User\UserSettings;

use App\Repositories\Base\BaseRepositoryInterface;

interface UserSettingsRepositoryInterface extends BaseRepositoryInterface
{
    public function findByUserUuid(string $userUuid);
    public function createDefaultSettings(string $userUuid);
}
