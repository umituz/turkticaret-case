<?php

namespace App\Observers\Auth;

use App\Models\User\User;
use App\Observers\Base\BaseObserver;
use App\Repositories\User\UserSettings\UserSettingsRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class UserObserver extends BaseObserver
{
    public function __construct(protected UserSettingsRepositoryInterface $userSettingsRepository) {}

    public function created(Model $model): void
    {
        $this->userSettingsRepository->createDefaultSettings($model->uuid);
    }
}
