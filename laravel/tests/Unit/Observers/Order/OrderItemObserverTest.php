<?php

namespace Tests\Unit\Observers\Order;

use App\Observers\Base\BaseObserver;
use App\Observers\Order\OrderItemObserver;
use Tests\Base\BaseObserverUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(OrderItemObserver::class)]
class OrderItemObserverTest extends BaseObserverUnitTest
{
    protected function getObserverClass(): string
    {
        return OrderItemObserver::class;
    }

    #[Test]
    public function it_extends_base_observer(): void
    {
        $this->assertExtendsObserverBase(BaseObserver::class);
    }

    #[Test]
    public function it_inherits_all_base_observer_methods(): void
    {
        $this->assertObserverHasMethod('creating');
        $this->assertObserverHasMethod('created');
        $this->assertObserverHasMethod('updated');
        $this->assertObserverHasMethod('deleted');
        $this->assertObserverHasMethod('restored');
    }
}