<?php

namespace App\Http\Resources\User\UserSettings;

use App\Http\Resources\Base\BaseCollection;

class UserSettingsCollection extends BaseCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = UserSettingsResource::class;
}