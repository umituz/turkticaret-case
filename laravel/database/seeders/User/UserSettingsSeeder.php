<?php

namespace Database\Seeders\User;

use App\Enums\User\UserTypeEnum;
use App\Models\User\User;
use App\Models\User\UserSetting;
use Illuminate\Database\Seeder;

class UserSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $this->createUserSettings();
    }

    private function createUserSettings(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            $this->createSettingsForUser($user);
        }

        $this->command->info('User settings seeded successfully!');
        $this->command->info("Created settings for {$users->count()} users");
    }

    private function createSettingsForUser(User $user): void
    {
        // Check if user already has settings
        $existingSettings = UserSetting::where('user_uuid', $user->uuid)->exists();

        if ($existingSettings) {
            return;
        }

        // Determine if this is an admin user based on email
        $isAdmin = $user->email === UserTypeEnum::ADMIN->getEmail();

        // Create settings using factory with appropriate state
        if ($isAdmin) {
            UserSetting::factory()
                ->adminDefaults()
                ->create(['user_uuid' => $user->uuid]);
        } else {
            UserSetting::factory()
                ->userDefaults()
                ->create(['user_uuid' => $user->uuid]);
        }
    }
}
