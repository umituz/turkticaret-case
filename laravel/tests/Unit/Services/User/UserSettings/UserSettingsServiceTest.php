<?php

namespace Tests\Unit\Services\User\UserSettings;

use App\Services\User\UserSettings\UserSettingsService;
use App\Repositories\User\UserSettings\UserSettingsRepositoryInterface;
use App\Models\User\User;
use App\Models\User\UserSetting;
use App\DTOs\User\NotificationPreferencesDTO;
use App\DTOs\User\UserPreferencesDTO;
use App\DTOs\User\PasswordChangeDTO;
use Tests\Base\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Illuminate\Support\Facades\Hash;
use Mockery;

/**
 * Unit tests for UserSettingsService
 * Tests user settings management with repository mocking
 */
#[CoversClass(UserSettingsService::class)]
#[Group('unit')]
#[Group('services')]
#[Small]
class UserSettingsServiceTest extends UnitTestCase
{
    private UserSettingsService $service;
    private UserSettingsRepositoryInterface $mockRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockRepository = Mockery::mock(UserSettingsRepositoryInterface::class);
        $this->service = new UserSettingsService($this->mockRepository);
    }

    #[Test]
    public function get_user_settings_returns_existing_settings(): void
    {
        // Arrange
        $user = Mockery::mock(User::class);
        $userSetting = Mockery::mock(UserSetting::class);
        $user->userSettings = $userSetting;

        // Act
        $result = $this->service->getUserSettings($user);

        // Assert
        $this->assertSame($userSetting, $result);
    }

    #[Test]
    public function get_user_settings_creates_default_when_none_exist(): void
    {
        // Arrange
        $user = Mockery::mock(User::class);
        $user->userSettings = null;
        $user->uuid = 'test-user-uuid';
        
        $defaultSettings = Mockery::mock(UserSetting::class);
        
        // Mock UserSetting::where to return no existing settings
        UserSetting::shouldReceive('where')
            ->once()
            ->with('user_uuid', $user->uuid)
            ->andReturnSelf();
        UserSetting::shouldReceive('first')
            ->once()
            ->andReturn(null);

        $this->mockRepository->shouldReceive('createDefaultSettings')
            ->once()
            ->with($user->uuid)
            ->andReturn($defaultSettings);
            
        $user->shouldReceive('load')
            ->once()
            ->with('userSettings');

        // Act
        $result = $this->service->getUserSettings($user);

        // Assert
        $this->assertSame($defaultSettings, $result);
    }

    #[Test]
    public function create_default_settings_returns_existing_if_found(): void
    {
        // Arrange
        $user = Mockery::mock(User::class);
        $user->uuid = 'test-user-uuid';
        
        $existingSettings = Mockery::mock(UserSetting::class);
        
        UserSetting::shouldReceive('where')
            ->once()
            ->with('user_uuid', $user->uuid)
            ->andReturnSelf();
        UserSetting::shouldReceive('first')
            ->once()
            ->andReturn($existingSettings);

        // Act
        $result = $this->service->createDefaultSettings($user);

        // Assert
        $this->assertSame($existingSettings, $result);
    }

    #[Test]
    public function create_default_settings_creates_new_when_none_exist(): void
    {
        // Arrange
        $user = Mockery::mock(User::class);
        $user->uuid = 'test-user-uuid';
        
        $newSettings = Mockery::mock(UserSetting::class);
        
        UserSetting::shouldReceive('where')
            ->once()
            ->with('user_uuid', $user->uuid)
            ->andReturnSelf();
        UserSetting::shouldReceive('first')
            ->once()
            ->andReturn(null);

        $this->mockRepository->shouldReceive('createDefaultSettings')
            ->once()
            ->with($user->uuid)
            ->andReturn($newSettings);
            
        $user->shouldReceive('load')
            ->once()
            ->with('userSettings');

        // Act
        $result = $this->service->createDefaultSettings($user);

        // Assert
        $this->assertSame($newSettings, $result);
    }

    #[Test]
    public function update_notification_preferences_updates_existing_settings(): void
    {
        // Arrange
        $user = Mockery::mock(User::class);
        $userSetting = Mockery::mock(UserSetting::class);
        $user->userSettings = $userSetting;
        
        $preferences = Mockery::mock(NotificationPreferencesDTO::class);
        $preferencesArray = ['email_notifications' => true, 'push_notifications' => false];
        $preferences->shouldReceive('toArray')
            ->once()
            ->andReturn($preferencesArray);
            
        $freshSettings = Mockery::mock(UserSetting::class);
        
        $userSetting->shouldReceive('update')
            ->once()
            ->with($preferencesArray);
        $userSetting->shouldReceive('fresh')
            ->once()
            ->andReturn($freshSettings);

        // Act
        $result = $this->service->updateNotificationPreferences($user, $preferences);

        // Assert
        $this->assertSame($freshSettings, $result);
    }

    #[Test]
    public function update_notification_preferences_creates_settings_when_none_exist(): void
    {
        // Arrange
        $user = Mockery::mock(User::class);
        $user->userSettings = null;
        $user->uuid = 'test-user-uuid';
        
        $preferences = Mockery::mock(NotificationPreferencesDTO::class);
        $preferencesArray = ['email_notifications' => true, 'push_notifications' => false];
        $preferences->shouldReceive('toArray')
            ->once()
            ->andReturn($preferencesArray);
        
        $defaultSettings = Mockery::mock(UserSetting::class);
        $freshSettings = Mockery::mock(UserSetting::class);
        
        UserSetting::shouldReceive('where')
            ->once()
            ->with('user_uuid', $user->uuid)
            ->andReturnSelf();
        UserSetting::shouldReceive('first')
            ->once()
            ->andReturn(null);

        $this->mockRepository->shouldReceive('createDefaultSettings')
            ->once()
            ->with($user->uuid)
            ->andReturn($defaultSettings);
            
        $user->shouldReceive('load')
            ->once()
            ->with('userSettings');
            
        $defaultSettings->shouldReceive('update')
            ->once()
            ->with($preferencesArray);
        $defaultSettings->shouldReceive('fresh')
            ->once()
            ->andReturn($freshSettings);

        // Act
        $result = $this->service->updateNotificationPreferences($user, $preferences);

        // Assert
        $this->assertSame($freshSettings, $result);
    }

    #[Test]
    public function update_user_preferences_updates_user(): void
    {
        // Arrange
        $user = Mockery::mock(User::class);
        $preferences = Mockery::mock(UserPreferencesDTO::class);
        $preferencesArray = ['first_name' => 'John', 'last_name' => 'Doe'];
        $preferences->shouldReceive('toArray')
            ->once()
            ->andReturn($preferencesArray);
            
        $freshUser = Mockery::mock(User::class);
        
        $user->shouldReceive('update')
            ->once()
            ->with($preferencesArray);
        $user->shouldReceive('fresh')
            ->once()
            ->andReturn($freshUser);

        // Act
        $result = $this->service->updateUserPreferences($user, $preferences);

        // Assert
        $this->assertSame($freshUser, $result);
    }

    #[Test]
    public function change_password_returns_false_when_current_password_invalid(): void
    {
        // Arrange
        $user = Mockery::mock(User::class);
        $user->password = 'hashed_password';
        
        $passwordChange = Mockery::mock(PasswordChangeDTO::class);
        $passwordChange->currentPassword = 'wrong_password';
        $passwordChange->newPassword = 'new_password';
        
        Hash::shouldReceive('check')
            ->once()
            ->with('wrong_password', 'hashed_password')
            ->andReturn(false);

        // Act
        $result = $this->service->changePassword($user, $passwordChange);

        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    public function change_password_returns_true_when_password_changed_successfully(): void
    {
        // Arrange
        $user = Mockery::mock(User::class);
        $user->password = 'hashed_password';
        
        $passwordChange = Mockery::mock(PasswordChangeDTO::class);
        $passwordChange->currentPassword = 'current_password';
        $passwordChange->newPassword = 'new_password';
        
        Hash::shouldReceive('check')
            ->once()
            ->with('current_password', 'hashed_password')
            ->andReturn(true);
            
        $user->shouldReceive('update')
            ->once()
            ->with(['password' => 'new_password']);

        // Act
        $result = $this->service->changePassword($user, $passwordChange);

        // Assert
        $this->assertTrue($result);
    }
}