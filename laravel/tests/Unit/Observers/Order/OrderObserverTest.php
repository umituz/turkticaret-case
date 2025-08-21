<?php

namespace Tests\Unit\Observers\Order;

use App\Models\Order\Order;
use App\Observers\Base\BaseObserver;
use App\Observers\Order\OrderObserver;
use Tests\Base\BaseObserverUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Mockery;

#[CoversClass(OrderObserver::class)]
class OrderObserverTest extends BaseObserverUnitTest
{
    protected function getObserverClass(): string
    {
        return OrderObserver::class;
    }

    #[Test]
    public function it_extends_base_observer(): void
    {
        $this->assertExtendsObserverBase(BaseObserver::class);
    }

    #[Test]
    public function it_generates_order_number_on_creating_when_not_set(): void
    {
        $model = new Order();
        $model->order_number = '';
        $model->uuid = null;
        
        $this->observer->creating($model);
        
        // Check UUID was generated
        $this->assertNotNull($model->uuid);
        
        // Check order number was generated with correct format
        $this->assertNotEmpty($model->order_number);
        $this->assertStringStartsWith('ORD-' . date('Ymd') . '-', $model->order_number);
        $this->assertMatchesRegularExpression('/^ORD-\d{8}-[a-f0-9]{8}$/', $model->order_number);
    }

    #[Test]
    public function it_does_not_override_existing_order_number(): void
    {
        $existingOrderNumber = 'ORD-20240101-12345678';
        $model = new Order();
        $model->order_number = $existingOrderNumber;
        $model->uuid = null;

        $this->observer->creating($model);

        // UUID should be generated
        $this->assertNotNull($model->uuid);
        
        // Order number should remain unchanged
        $this->assertEquals($existingOrderNumber, $model->order_number);
    }

    #[Test]
    public function it_only_generates_order_number_for_order_model(): void
    {
        $model = new \App\Models\Product\Product();
        $model->uuid = null;

        $this->observer->creating($model);

        // UUID should be generated
        $this->assertNotNull($model->uuid);
        
        // Order number should not be set on non-Order models
        $this->assertObjectNotHasProperty('order_number', $model);
    }

    #[Test]
    public function it_calls_parent_creating_method(): void
    {
        $model = new Order();
        $model->order_number = 'existing';
        $model->uuid = 'existing-uuid';

        $this->observer->creating($model);

        // Should not change existing values
        $this->assertEquals('existing', $model->order_number);
        $this->assertEquals('existing-uuid', $model->uuid);
    }

    #[Test]
    public function it_has_creating_method(): void
    {
        $this->assertObserverHasMethod('creating');
    }
}