<?php

namespace App\DTOs\User;

/**
 * Data Transfer Object for user notification preferences
 * 
 * Manages user notification settings across multiple channels and categories.
 * This DTO provides type safety and default values for notification preferences,
 * ensuring a consistent interface for managing user communication preferences.
 * 
 * @property bool $email Email notification preference (default: true)
 * @property bool $push Push notification preference (default: true)
 * @property bool $sms SMS notification preference (default: false)
 * @property bool $marketing Marketing communication preference (default: false)
 * @property bool $orderUpdates Order update notification preference (default: true)
 * @property bool $newsletter Newsletter subscription preference (default: false)
 */
readonly class NotificationPreferencesDTO
{
    /**
     * Create a new NotificationPreferencesDTO instance
     * 
     * All parameters have sensible defaults that prioritize essential notifications
     * while respecting user privacy for marketing communications.
     * 
     * @param bool $email Enable/disable email notifications
     * @param bool $push Enable/disable push notifications
     * @param bool $sms Enable/disable SMS notifications
     * @param bool $marketing Enable/disable marketing communications
     * @param bool $orderUpdates Enable/disable order update notifications
     * @param bool $newsletter Enable/disable newsletter subscription
     */
    public function __construct(
        public bool $email = true,
        public bool $push = true,
        public bool $sms = false,
        public bool $marketing = false,
        public bool $orderUpdates = true,
        public bool $newsletter = false,
    ) {}

    /**
     * Create NotificationPreferencesDTO from request array
     * 
     * Factory method that creates an instance from user input data.
     * Provides default values for any missing preferences to ensure
     * complete preference sets are always maintained.
     * 
     * @param array $data User preference data from request
     * @return self New NotificationPreferencesDTO instance with defaults applied
     */
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

    /**
     * Convert DTO to array for database storage
     * 
     * Transforms the notification preferences to snake_case format suitable
     * for database storage and API responses. The key names are expanded
     * to be more descriptive for better database clarity.
     * 
     * @return array Array representation with descriptive snake_case keys
     */
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