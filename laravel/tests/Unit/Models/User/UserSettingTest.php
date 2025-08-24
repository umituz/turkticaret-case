<?php

namespace Tests\Unit\Models\User;

use App\Models\User\UserSetting;
use App\Models\User\User;
use Tests\Base\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;

/**
 * Unit tests for UserSetting Model
 * Tests model attributes, casts, and relationships
 */
#[CoversClass(UserSetting::class)]
#[Group('unit')]
#[Group('models')]
#[Small]
class UserSettingTest extends UnitTestCase
{
    #[Test]
    public function model_has_correct_fillable_attributes(): void
    {
        // Arrange & Act
        $userSetting = new UserSetting();
        
        // Assert
        $expectedFillable = [
            'user_uuid',
            'email_notifications',
            'push_notifications',
            'sms_notifications',
            'marketing_notifications',
            'order_update_notifications',
            'newsletter_notifications',
        ];
        
        $this->assertEquals($expectedFillable, $userSetting->getFillable());
    }

    #[Test]
    public function model_has_correct_casts(): void
    {
        // Arrange & Act
        $userSetting = new UserSetting();
        $casts = $userSetting->getCasts();
        
        // Assert - Check specific notification casts
        $this->assertEquals('boolean', $casts['email_notifications']);
        $this->assertEquals('boolean', $casts['push_notifications']);
        $this->assertEquals('boolean', $casts['sms_notifications']);
        $this->assertEquals('boolean', $casts['marketing_notifications']);
        $this->assertEquals('boolean', $casts['order_update_notifications']);
        $this->assertEquals('boolean', $casts['newsletter_notifications']);
        
        // Verify we have the base datetime casts
        $this->assertEquals('datetime', $casts['deleted_at']); // From SoftDeletes
        
        // Verify cast count
        $this->assertGreaterThanOrEqual(7, count($casts));
    }

    #[Test]
    public function model_extends_base_uuid_model(): void
    {
        // Arrange & Act
        $userSetting = new UserSetting();
        
        // Assert
        $this->assertInstanceOf(\App\Models\Base\BaseUuidModel::class, $userSetting);
    }

    #[Test]
    public function model_uses_correct_table_name(): void
    {
        // Arrange & Act
        $userSetting = new UserSetting();
        
        // Assert
        $this->assertEquals('user_settings', $userSetting->getTable());
    }

    #[Test]
    public function user_setting_can_be_created_with_all_notification_preferences(): void
    {
        // Arrange
        $attributes = [
            'user_uuid' => 'test-user-uuid',
            'email_notifications' => true,
            'push_notifications' => false,
            'sms_notifications' => true,
            'marketing_notifications' => false,
            'order_update_notifications' => true,
            'newsletter_notifications' => false,
        ];

        // Act
        $userSetting = new UserSetting($attributes);

        // Assert
        $this->assertEquals('test-user-uuid', $userSetting->user_uuid);
        $this->assertTrue($userSetting->email_notifications);
        $this->assertFalse($userSetting->push_notifications);
        $this->assertTrue($userSetting->sms_notifications);
        $this->assertFalse($userSetting->marketing_notifications);
        $this->assertTrue($userSetting->order_update_notifications);
        $this->assertFalse($userSetting->newsletter_notifications);
    }

    #[Test]
    public function model_has_user_relationship(): void
    {
        // Arrange & Act
        $userSetting = new UserSetting();
        $relationship = $userSetting->user();
        
        // Assert
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relationship);
        $this->assertEquals('user_uuid', $relationship->getForeignKeyName());
        $this->assertEquals('uuid', $relationship->getOwnerKeyName());
    }

    #[Test]
    public function model_uses_has_factory_trait(): void
    {
        // Arrange & Act & Assert
        $this->assertTrue(
            in_array(\Illuminate\Database\Eloquent\Factories\HasFactory::class, class_uses(UserSetting::class)),
            'UserSetting model should use HasFactory trait'
        );
    }

    #[Test]
    public function all_notification_attributes_are_boolean_castable(): void
    {
        // Arrange
        $userSetting = new UserSetting([
            'user_uuid' => 'test-uuid',
            'email_notifications' => 1,
            'push_notifications' => 0,
            'sms_notifications' => '1',
            'marketing_notifications' => '0',
            'order_update_notifications' => 'true',
            'newsletter_notifications' => 'false',
        ]);

        // Act & Assert - All should be cast to proper booleans
        $this->assertIsBool($userSetting->email_notifications);
        $this->assertIsBool($userSetting->push_notifications);
        $this->assertIsBool($userSetting->sms_notifications);
        $this->assertIsBool($userSetting->marketing_notifications);
        $this->assertIsBool($userSetting->order_update_notifications);
        $this->assertIsBool($userSetting->newsletter_notifications);
        
        // Check actual values
        $this->assertTrue($userSetting->email_notifications);
        $this->assertFalse($userSetting->push_notifications);
        $this->assertTrue($userSetting->sms_notifications);
        $this->assertFalse($userSetting->marketing_notifications);
    }

    #[Test]
    public function model_has_correct_default_notification_behavior(): void
    {
        // Arrange & Act
        $userSetting = new UserSetting(['user_uuid' => 'test-uuid']);

        // Assert - Should not have values until explicitly set
        $this->assertNull($userSetting->getRawOriginal('email_notifications'));
        $this->assertNull($userSetting->getRawOriginal('push_notifications'));
        $this->assertNull($userSetting->getRawOriginal('sms_notifications'));
    }
}