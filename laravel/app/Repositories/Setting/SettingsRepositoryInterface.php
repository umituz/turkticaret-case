<?php

namespace App\Repositories\Setting;

use App\Enums\Setting\SettingKeyEnum;
use App\Repositories\Base\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

interface SettingsRepositoryInterface extends BaseRepositoryInterface
{
    public function getAllActive(): Collection;

    public function updateByKey(SettingKeyEnum $key, mixed $value): bool;
}