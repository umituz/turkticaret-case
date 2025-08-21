<?php

namespace Tests\Unit\Observers\Cart;

use App\Observers\Base\BaseObserver;
use App\Observers\Cart\CartObserver;
use Tests\Base\BaseObserverUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(CartObserver::class)]
class CartObserverTest extends BaseObserverUnitTest
{
    protected function getObserverClass(): string
    {
        return CartObserver::class;
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