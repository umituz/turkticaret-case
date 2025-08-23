<?php

namespace App\Models\User;

use App\Models\Base\BaseUuidModel;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
     * Get the user that owns the settings
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }
}
