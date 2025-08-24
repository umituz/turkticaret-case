<?php

namespace App\Observers\Auth;

use App\Models\User\User;
use App\Observers\Base\BaseObserver;
use App\Repositories\User\UserSettings\UserSettingsRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * Observer for User model events.
 * 
 * This observer handles User model lifecycle events including
 * automatic creation of default user settings after user creation.
 *
 * @package App\Observers\Auth
 */
class UserObserver extends BaseObserver
{
    /**
     * Create a new UserObserver instance.
     *
     * @param UserSettingsRepositoryInterface $userSettingsRepository The user settings repository
     */
    public function __construct(protected UserSettingsRepositoryInterface $userSettingsRepository) {}

    /**
     * Handle the User "created" event.
     * Creates default settings for the newly created user.
     *
     * @param Model $model The User model instance
     * @return void
     */
    public function created(Model $model): void
    {
        $this->userSettingsRepository->createDefaultSettings($model->uuid);
    }
}
