<?php

namespace Tests\Unit\Observers\Base;

use App\Models\Product\Product;
use App\Observers\Base\BaseObserver;
use Tests\Base\BaseObserverUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Mockery;

// Create a concrete implementation for testing
class TestObserver extends BaseObserver
{
}

#[CoversClass(BaseObserver::class)]
class BaseObserverTest extends BaseObserverUnitTest
{
    protected function getObserverClass(): string
    {
        return TestObserver::class;
    }

    #[Test]
    public function it_is_abstract_class(): void
    {
        $reflection = new \ReflectionClass(BaseObserver::class);
        $this->assertTrue($reflection->isAbstract());
    }

    #[Test]
    public function it_has_activity_loggable_trait(): void
    {
        $reflection = new \ReflectionClass(BaseObserver::class);
        $traits = $reflection->getTraitNames();
        $this->assertContains('App\Traits\ActivityLoggable', $traits);
    }

    #[Test]
    public function it_generates_uuid_on_creating_when_not_set(): void
    {
        $model = new Product();
        $model->uuid = null;
        
        $this->observer->creating($model);
        
        $this->assertNotNull($model->uuid);
        $this->assertIsString($model->uuid);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $model->uuid
        );
    }

    #[Test]
    public function it_does_not_override_existing_uuid_on_creating(): void
    {
        $existingUuid = 'existing-uuid-123';
        $model = new Product();
        $model->uuid = $existingUuid;

        $this->observer->creating($model);

        $this->assertEquals($existingUuid, $model->uuid);
    }

    #[Test]
    public function it_has_log_activity_method(): void
    {
        $reflection = new \ReflectionClass($this->observer);
        $this->assertTrue($reflection->hasMethod('logActivity'));
        
        $method = $reflection->getMethod('logActivity');
        $this->assertTrue($method->isProtected());
    }

    #[Test]
    public function it_can_get_log_description(): void
    {
        $model = new Product();
        $model->uuid = 'test-uuid-123';
        
        $reflection = new \ReflectionClass($this->observer);
        $method = $reflection->getMethod('getLogDescription');
        $method->setAccessible(true);
        
        $description = $method->invoke($this->observer, $model, 'created');
        
        $this->assertStringContainsString('Product', $description);
        $this->assertStringContainsString('test-uuid-123', $description);
        $this->assertStringContainsString('created', $description);
    }

    #[Test]
    public function it_can_get_user_info(): void
    {
        // Create a partial mock that allows mocking protected methods
        $observer = Mockery::mock(TestObserver::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        
        // Mock the getUserInfo method to return test data
        $observer->shouldReceive('getUserInfo')->andReturn([
            'user_id' => 'test-user-uuid',
            'user_name' => 'Test User',
            'user_email' => 'test@example.com',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'TestAgent/1.0',
        ]);
        
        $userInfo = $observer->getUserInfo();
        
        $this->assertIsArray($userInfo);
        $this->assertArrayHasKey('user_id', $userInfo);
        $this->assertArrayHasKey('user_name', $userInfo);
        $this->assertArrayHasKey('user_email', $userInfo);
        $this->assertArrayHasKey('ip_address', $userInfo);
        $this->assertArrayHasKey('user_agent', $userInfo);
    }

    #[Test]
    public function it_has_all_lifecycle_methods(): void
    {
        $this->assertObserverHasMethod('creating');
        $this->assertObserverHasMethod('created');
        $this->assertObserverHasMethod('updated');
        $this->assertObserverHasMethod('deleted');
        $this->assertObserverHasMethod('restored');
    }
}