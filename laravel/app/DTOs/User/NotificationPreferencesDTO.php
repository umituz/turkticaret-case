<?php

namespace App\DTOs\User;

readonly class NotificationPreferencesDTO
{
    public function __construct(
        public bool $email = true,
        public bool $push = true,
        public bool $sms = false,
        public bool $marketing = false,
        public bool $orderUpdates = true,
        public bool $newsletter = false,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'] ?? true,
            push: $data['push'] ?? true,
            sms: $data['sms'] ?? false,
            marketing: $data['marketing'] ?? false,
            orderUpdates: $data['orderUpdates'] ?? true,
            newsletter: $data['newsletter'] ?? false,
        );
    }

    public function toArray(): array
    {
        return [
            'email_notifications' => $this->email,
            'push_notifications' => $this->push,
            'sms_notifications' => $this->sms,
            'marketing_notifications' => $this->marketing,
            'order_update_notifications' => $this->orderUpdates,
            'newsletter_notifications' => $this->newsletter,
        ];
    }
}