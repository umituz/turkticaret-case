<?php

namespace App\Http\Resources\User\UserSettings;

use App\Http\Resources\Base\BaseResource;

class UserSettingsResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'notifications' => [
                'email' => $this->email_notifications,
                'push' => $this->push_notifications,
                'sms' => $this->sms_notifications,
                'marketing' => $this->marketing_notifications,
                'orderUpdates' => $this->order_update_notifications,
                'newsletter' => $this->newsletter_notifications,
            ],
            'preferences' => [
                'language' => $this->whenLoaded('user', fn() => $this->user->language_uuid),
                'timezone' => $this->whenLoaded('user', fn() => $this->user->timezone ?? 'UTC'),
            ]
        ];
    }
}