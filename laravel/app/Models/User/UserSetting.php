<?php

namespace App\Models\User;

use App\Models\Base\BaseUuidModel;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * UserSetting Model for managing user notification preferences.
 * 
 * Handles user-specific notification settings including email, push, SMS,
 * marketing, order updates, and newsletter preferences. Provides granular
 * control over user communication preferences.
 *
 * @property string $uuid Settings unique identifier
 * @property string $user_uuid Associated user UUID
 * @property bool $email_notifications Whether email notifications are enabled
 * @property bool $push_notifications Whether push notifications are enabled
 * @property bool $sms_notifications Whether SMS notifications are enabled
 * @property bool $marketing_notifications Whether marketing communications are enabled
 * @property bool $order_update_notifications Whether order update notifications are enabled
 * @property bool $newsletter_notifications Whether newsletter subscriptions are enabled
 * @property \Carbon\Carbon $created_at Creation timestamp
 * @property \Carbon\Carbon $updated_at Last update timestamp
 * @property \Carbon\Carbon|null $deleted_at Soft deletion timestamp
 * 
 * @package App\Models\User
 */
class UserSetting extends BaseUuidModel
{
    use HasFactory;

    protected $table = 'user_settings';

    protected $fillable = [
        'user_uuid',
        'email_notifications',
        'push_notifications',
        'sms_notifications',
        'marketing_notifications',
        'order_update_notifications',
        'newsletter_notifications',
    ];

    protected $casts = [
        'email_notifications' => 'boolean',
        'push_notifications' => 'boolean',
        'sms_notifications' => 'boolean',
        'marketing_notifications' => 'boolean',
        'order_update_notifications' => 'boolean',
        'newsletter_notifications' => 'boolean',
    ];

    /**
     * Get the user that owns these settings.
     *
     * @return BelongsTo<User>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }
}
