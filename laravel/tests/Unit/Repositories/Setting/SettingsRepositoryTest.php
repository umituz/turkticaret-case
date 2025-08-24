<?php

namespace Tests\Unit\Repositories\Setting;

use App\Repositories\Setting\SettingsRepository;
use App\Models\Setting\Setting;
use App\Enums\Setting\SettingKeyEnum;
use Tests\Base\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Illuminate\Database\Eloquent\Collection;
use Mockery;

/**
 * Unit tests for SettingsRepository
 * Tests repository methods with model mocking
 */
#[CoversClass(SettingsRepository::class)]
#[Group('unit')]
#[Group('repositories')]
#[Small]
class SettingsRepositoryTest extends UnitTestCase
{
    private SettingsRepository $repository;
    private Setting $mockModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockModel = Mockery::mock(Setting::class);
        $this->repository = new SettingsRepository($this->mockModel);
    }

    #[Test]
    public function constructor_sets_model_correctly(): void
    {
        // Arrange & Act
        $repository = new SettingsRepository($this->mockModel);
        
        // Assert
        $this->assertInstanceOf(SettingsRepository::class, $repository);
    }

    #[Test]
    public function get_all_active_returns_active_settings_collection(): void
    {
        // Arrange
        $mockQuery = Mockery::mock();
        $expectedCollection = Mockery::mock(Collection::class);
        
        $this->mockModel->shouldReceive('active')
            ->once()
            ->andReturn($mockQuery);
            
        $mockQuery->shouldReceive('get')
            ->once()
            ->andReturn($expectedCollection);

        // Act
        $result = $this->repository->getAllActive();

        // Assert
        $this->assertSame($expectedCollection, $result);
    }

    #[Test]
    public function update_by_key_updates_editable_setting_and_returns_true(): void
    {
        // Arrange
        $key = SettingKeyEnum::DEFAULT_CURRENCY;
        $value = 'EUR';
        $mockQuery1 = Mockery::mock();
        $mockQuery2 = Mockery::mock();
        
        $this->mockModel->shouldReceive('byKey')
            ->once()
            ->with($key->value)
            ->andReturn($mockQuery1);
            
        $mockQuery1->shouldReceive('where')
            ->once()
            ->with('is_editable', true)
            ->andReturn($mockQuery2);
            
        $mockQuery2->shouldReceive('update')
            ->once()
            ->with(['value' => $value])
            ->andReturn(1);

        // Act
        $result = $this->repository->updateByKey($key, $value);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function update_by_key_returns_false_when_no_editable_setting_found(): void
    {
        // Arrange
        $key = SettingKeyEnum::APP_NAME;
        $value = 'New App Name';
        $mockQuery1 = Mockery::mock();
        $mockQuery2 = Mockery::mock();
        
        $this->mockModel->shouldReceive('byKey')
            ->once()
            ->with($key->value)
            ->andReturn($mockQuery1);
            
        $mockQuery1->shouldReceive('where')
            ->once()
            ->with('is_editable', true)
            ->andReturn($mockQuery2);
            
        $mockQuery2->shouldReceive('update')
            ->once()
            ->with(['value' => $value])
            ->andReturn(0);

        // Act
        $result = $this->repository->updateByKey($key, $value);

        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    public function update_by_key_works_with_different_enum_keys(): void
    {
        // Arrange
        $key = SettingKeyEnum::DEFAULT_LANGUAGE;
        $value = 'tr';
        $mockQuery1 = Mockery::mock();
        $mockQuery2 = Mockery::mock();
        
        $this->mockModel->shouldReceive('byKey')
            ->once()
            ->with($key->value)
            ->andReturn($mockQuery1);
            
        $mockQuery1->shouldReceive('where')
            ->once()
            ->with('is_editable', true)
            ->andReturn($mockQuery2);
            
        $mockQuery2->shouldReceive('update')
            ->once()
            ->with(['value' => $value])
            ->andReturn(1);

        // Act
        $result = $this->repository->updateByKey($key, $value);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function update_by_key_handles_mixed_value_types(): void
    {
        // Arrange
        $key = SettingKeyEnum::ITEMS_PER_PAGE;
        $value = 25; // Integer value
        $mockQuery1 = Mockery::mock();
        $mockQuery2 = Mockery::mock();
        
        $this->mockModel->shouldReceive('byKey')
            ->once()
            ->with($key->value)
            ->andReturn($mockQuery1);
            
        $mockQuery1->shouldReceive('where')
            ->once()
            ->with('is_editable', true)
            ->andReturn($mockQuery2);
            
        $mockQuery2->shouldReceive('update')
            ->once()
            ->with(['value' => $value])
            ->andReturn(1);

        // Act
        $result = $this->repository->updateByKey($key, $value);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function repository_implements_settings_repository_interface(): void
    {
        // Arrange & Act & Assert
        $this->assertInstanceOf(
            \App\Repositories\Setting\SettingsRepositoryInterface::class, 
            $this->repository
        );
    }

    #[Test]
    public function repository_extends_base_repository(): void
    {
        // Arrange & Act & Assert
        $this->assertInstanceOf(
            \App\Repositories\Base\BaseRepository::class, 
            $this->repository
        );
    }
}