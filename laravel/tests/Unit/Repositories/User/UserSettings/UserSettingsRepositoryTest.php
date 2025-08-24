<?php

namespace Tests\Unit\Repositories\User\UserSettings;

use App\Repositories\User\UserSettings\UserSettingsRepository;
use App\Repositories\User\UserSettings\UserSettingsRepositoryInterface;
use App\Models\User\UserSetting;
use Tests\Base\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;

/**
 * Unit tests for UserSettingsRepository
 * Tests repository structure and interface implementation
 */
#[CoversClass(UserSettingsRepository::class)]
#[Group('unit')]
#[Group('repositories')]
#[Small]
class UserSettingsRepositoryTest extends UnitTestCase
{
    private UserSettingsRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new UserSettingsRepository(new UserSetting());
    }

    #[Test]
    public function repository_has_required_constructor_dependencies(): void
    {
        // Act
        $repository = new UserSettingsRepository(new UserSetting());

        // Assert
        $this->assertInstanceOf(UserSettingsRepository::class, $repository);
    }

    #[Test]
    public function repository_has_required_methods(): void
    {
        // Assert
        $this->assertTrue(method_exists($this->repository, 'findByUserAndKey'));
        $this->assertTrue(method_exists($this->repository, 'findByUser'));
        $this->assertTrue(method_exists($this->repository, 'updateOrCreateSetting'));
    }

    #[Test]
    public function it_extends_base_repository(): void
    {
        // Assert
        $this->assertInstanceOf(\App\Repositories\Base\BaseRepository::class, $this->repository);
    }

    #[Test]
    public function it_implements_interface(): void
    {
        // Assert
        $this->assertInstanceOf(UserSettingsRepositoryInterface::class, $this->repository);
    }

    #[Test]
    public function it_uses_correct_model(): void
    {
        // Act
        $model = $this->repository->getModel();

        // Assert
        $this->assertInstanceOf(UserSetting::class, $model);
    }

    #[Test]
    public function repository_constructor_accepts_model(): void
    {
        // Arrange
        $model = new UserSetting();

        // Act
        $repository = new UserSettingsRepository($model);

        // Assert
        $this->assertInstanceOf(UserSettingsRepository::class, $repository);
    }

    #[Test]
    public function repository_inherits_base_functionality(): void
    {
        // Assert - test inherited methods from BaseRepository
        $this->assertTrue(method_exists($this->repository, 'find'));
        $this->assertTrue(method_exists($this->repository, 'create'));
        $this->assertTrue(method_exists($this->repository, 'update'));
        $this->assertTrue(method_exists($this->repository, 'delete'));
        $this->assertTrue(method_exists($this->repository, 'paginate'));
    }

    #[Test]
    public function repository_works_with_user_setting_model(): void
    {
        // Assert
        $this->assertInstanceOf(UserSetting::class, $this->repository->getModel());
        $this->assertEquals(UserSetting::class, get_class($this->repository->getModel()));
    }

    #[Test]
    public function repository_maintains_model_relationship(): void
    {
        // Arrange
        $model = new UserSetting();
        $repository = new UserSettingsRepository($model);

        // Act
        $repositoryModel = $repository->getModel();

        // Assert
        $this->assertSame($model, $repositoryModel);
    }

    #[Test]
    public function repository_follows_repository_pattern(): void
    {
        // Assert
        $this->assertInstanceOf(UserSettingsRepositoryInterface::class, $this->repository);
        $this->assertInstanceOf(\App\Repositories\Base\BaseRepositoryInterface::class, $this->repository);
    }

    #[Test]
    public function repository_supports_user_settings_operations(): void
    {
        // Assert - user settings specific methods
        $this->assertTrue(method_exists($this->repository, 'findByUserAndKey'));
        $this->assertTrue(method_exists($this->repository, 'findByUser'));
        $this->assertTrue(method_exists($this->repository, 'updateOrCreateSetting'));
    }

    #[Test]
    public function repository_has_proper_method_visibility(): void
    {
        // Assert - check public methods are accessible
        $reflection = new \ReflectionClass($this->repository);
        
        $findByUserAndKeyMethod = $reflection->getMethod('findByUserAndKey');
        $findByUserMethod = $reflection->getMethod('findByUser');
        $updateOrCreateSettingMethod = $reflection->getMethod('updateOrCreateSetting');
        
        $this->assertTrue($findByUserAndKeyMethod->isPublic());
        $this->assertTrue($findByUserMethod->isPublic());
        $this->assertTrue($updateOrCreateSettingMethod->isPublic());
    }

    #[Test]
    public function repository_handles_user_settings_specific_operations(): void
    {
        // Assert - check method signatures exist
        $reflection = new \ReflectionClass($this->repository);
        
        $this->assertTrue($reflection->hasMethod('findByUserAndKey'));
        $this->assertTrue($reflection->hasMethod('findByUser'));
        $this->assertTrue($reflection->hasMethod('updateOrCreateSetting'));
    }
}