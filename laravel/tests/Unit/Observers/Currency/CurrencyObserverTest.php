<?php

namespace Tests\Unit\Observers\Currency;

use App\Observers\Currency\CurrencyObserver;
use App\Observers\Base\BaseObserver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(CurrencyObserver::class)]
class CurrencyObserverTest extends TestCase
{
    #[Test]
    public function it_extends_base_observer(): void
    {
        $observer = new CurrencyObserver();
        
        $this->assertInstanceOf(BaseObserver::class, $observer);
    }

    #[Test]
    public function it_inherits_all_base_observer_methods(): void
    {
        $observer = new CurrencyObserver();
        
        $this->assertTrue(method_exists($observer, 'creating'));
        $this->assertTrue(method_exists($observer, 'created'));
        $this->assertTrue(method_exists($observer, 'updated'));
        $this->assertTrue(method_exists($observer, 'deleted'));
    }
}