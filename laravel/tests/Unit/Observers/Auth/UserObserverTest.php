<?php

namespace Tests\Unit\Observers\Auth;

use App\Observers\Auth\UserObserver;
use App\Observers\Base\BaseObserver;
use Tests\Base\BaseObserverUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(UserObserver::class)]
class UserObserverTest extends BaseObserverUnitTest
{
    protected function getObserverClass(): string
    {
        return UserObserver::class;
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