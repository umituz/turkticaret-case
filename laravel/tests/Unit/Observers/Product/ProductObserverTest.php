<?php

namespace Tests\Unit\Observers\Product;

use App\Observers\Base\BaseObserver;
use App\Observers\Product\ProductObserver;
use Tests\Base\BaseObserverUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ProductObserver::class)]
class ProductObserverTest extends BaseObserverUnitTest
{
    protected function getObserverClass(): string
    {
        return ProductObserver::class;
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