<?php

namespace App\Repositories\User\UserSettings;

use App\Models\User\UserSetting;
use App\Repositories\Base\BaseRepository;

class UserSettingsRepository extends BaseRepository implements UserSettingsRepositoryInterface
{
    public function __construct(UserSetting $model)
    {
        parent::__construct($model);
    }

    public function findByUserUuid(string $userUuid): ?UserSetting
    {
        return $this->model->where('user_uuid', $userUuid)->first();
    }

    public function createDefaultSettings(string $userUuid): UserSetting
    {
        $existingSettings = $this->findByUserUuid($userUuid);
        if ($existingSettings) {
            return $existingSettings;
        }

        return $this->model->create([
            'user_uuid' => $userUuid,
            'email_notifications' => true,
            'push_notifications' => true,
            'sms_notifications' => false,
            'marketing_notifications' => false,
            'order_update_notifications' => true,
            'newsletter_notifications' => false,
        ]);
    }
}
