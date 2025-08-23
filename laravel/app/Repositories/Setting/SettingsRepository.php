<?php

namespace App\Repositories\Setting;

use App\Enums\Setting\SettingKeyEnum;
use App\Models\Setting\Setting;
use App\Repositories\Base\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class SettingsRepository extends BaseRepository implements SettingsRepositoryInterface
{
    public function __construct(Setting $model)
    {
        parent::__construct($model);
    }

    public function getAllActive(): Collection
    {
        return $this->model->active()->get();
    }

    public function updateByKey(SettingKeyEnum $key, mixed $value): bool
    {
        return $this->model->byKey($key->value)
            ->where('is_editable', true)
            ->update(['value' => $value]) > 0;
    }
}