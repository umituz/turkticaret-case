<?php

namespace Tests\Unit\Services\Setting;

use App\Enums\Setting\SettingKeyEnum;
use App\Models\Setting\Setting;
# Removed problematic interface import
use App\Services\Setting\SettingsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Base\BaseServiceUnitTest;

#[CoversClass(SettingsService::class)]
class SettingsServiceTest extends BaseServiceUnitTest
{
    protected $settingsRepository;

    protected function getServiceClass(): string
    {
        return SettingsService::class;
    }

    protected function getServiceDependencies(): array
    {
        $this->settingsRepository = Mockery::mock('App\Repositories\Setting\SettingsRepositoryInterface');
        return [$this->settingsRepository];
    }

    #[Test]
    public function it_gets_all_active_settings(): void
    {
        $settings = \Illuminate\Database\Eloquent\Collection::make([
            $this->createMockSetting(['key' => 'setting1', 'typed_value' => 'value1']),
            $this->createMockSetting(['key' => 'setting2', 'typed_value' => 'value2']),
        ]);

        $this->settingsRepository->shouldReceive('getAllActive')
            ->andReturn($settings);

        $result = $this->service->getAllActiveSettings();

        $this->assertEquals(['setting1' => 'value1', 'setting2' => 'value2'], $result->toArray());
    }

    #[Test]
    public function it_updates_setting_successfully(): void
    {
        $key = 'app_name';
        $value = 'New App Name';

        $settingKey = SettingKeyEnum::tryFrom($key);
        
        $this->settingsRepository->shouldReceive('updateByKey')
            ->with($settingKey, $value)
            ->andReturn(true);

        $result = $this->service->updateSetting($key, $value);

        $this->assertTrue($result);
    }

    #[Test] 
    public function it_fails_to_update_invalid_setting_key(): void
    {
        $key = 'invalid_key';
        $value = 'Some Value';

        $result = $this->service->updateSetting($key, $value);

        $this->assertFalse($result);
    }

    #[Test]
    public function it_updates_setting_with_repository_failure(): void
    {
        $key = 'app_name';
        $value = 'New App Name';

        $settingKey = SettingKeyEnum::tryFrom($key);
        
        $this->settingsRepository->shouldReceive('updateByKey')
            ->with($settingKey, $value)
            ->andReturn(false);

        $result = $this->service->updateSetting($key, $value);

        $this->assertFalse($result);
    }

    private function createMockSetting(array $attributes): object
    {
        $setting = new \stdClass();
        foreach ($attributes as $key => $value) {
            $setting->$key = $value;
        }
        return $setting;
    }
}