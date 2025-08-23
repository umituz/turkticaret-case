<?php

namespace Database\Factories\User;

use App\Models\User\User;
use App\Models\User\UserSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserSetting>
 */
class UserSettingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserSetting::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_uuid' => User::factory(),
            'email_notifications' => $this->faker->boolean(80), // 80% true
            'push_notifications' => $this->faker->boolean(70), // 70% true
            'sms_notifications' => $this->faker->boolean(30), // 30% true
            'marketing_notifications' => $this->faker->boolean(20), // 20% true
            'order_update_notifications' => $this->faker->boolean(90), // 90% true
            'newsletter_notifications' => $this->faker->boolean(40), // 40% true
        ];
    }

    /**
     * Create settings with all notifications enabled.
     */
    public function allNotificationsEnabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_notifications' => true,
            'push_notifications' => true,
            'sms_notifications' => true,
            'marketing_notifications' => true,
            'order_update_notifications' => true,
            'newsletter_notifications' => true,
        ]);
    }

    /**
     * Create settings with all notifications disabled.
     */
    public function allNotificationsDisabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_notifications' => false,
            'push_notifications' => false,
            'sms_notifications' => false,
            'marketing_notifications' => false,
            'order_update_notifications' => false,
            'newsletter_notifications' => false,
        ]);
    }

    /**
     * Create settings with only essential notifications enabled.
     */
    public function essentialOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_notifications' => true,
            'push_notifications' => false,
            'sms_notifications' => false,
            'marketing_notifications' => false,
            'order_update_notifications' => true,
            'newsletter_notifications' => false,
        ]);
    }

    /**
     * Create settings for admin users (more notifications enabled).
     */
    public function adminDefaults(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_notifications' => true,
            'push_notifications' => true,
            'sms_notifications' => true,
            'marketing_notifications' => false,
            'order_update_notifications' => true,
            'newsletter_notifications' => false,
        ]);
    }

    /**
     * Create settings for regular users.
     */
    public function userDefaults(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_notifications' => true,
            'push_notifications' => true,
            'sms_notifications' => false,
            'marketing_notifications' => false,
            'order_update_notifications' => true,
            'newsletter_notifications' => true,
        ]);
    }
}
