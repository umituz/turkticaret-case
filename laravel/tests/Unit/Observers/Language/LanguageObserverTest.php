<?php

namespace Tests\Unit\Observers\Language;

use App\Observers\Language\LanguageObserver;
use App\Observers\Base\BaseObserver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(LanguageObserver::class)]
class LanguageObserverTest extends TestCase
{
    #[Test]
    public function it_extends_base_observer(): void
    {
        $observer = new LanguageObserver();
        
        $this->assertInstanceOf(BaseObserver::class, $observer);
    }

    #[Test]
    public function it_inherits_all_base_observer_methods(): void
    {
        $observer = new LanguageObserver();
        
        $this->assertTrue(method_exists($observer, 'creating'));
        $this->assertTrue(method_exists($observer, 'created'));
        $this->assertTrue(method_exists($observer, 'updated'));
        $this->assertTrue(method_exists($observer, 'deleted'));
    }
}