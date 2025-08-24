<?php

namespace Tests\Unit\Jobs;

use App\Jobs\Order\SendOrderStatusUpdateEmail;
use App\Models\Order\Order;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use Tests\Base\UnitTestCase;

/**
 * Unit tests for SendOrderStatusUpdateEmail Job
 * Tests job structure and properties
 */
#[CoversClass(SendOrderStatusUpdateEmail::class)]
#[Group('unit')]
#[Group('jobs')]
#[Small]
class SendOrderStatusUpdateEmailTest extends UnitTestCase
{

    #[Test]
    public function it_can_be_instantiated_with_required_properties(): void
    {
        // Arrange
        $order = Mockery::mock(Order::class);
        $oldStatus = 'pending';
        $newStatus = 'confirmed';

        // Act
        $job = new SendOrderStatusUpdateEmail($order, $oldStatus, $newStatus);

        // Assert
        $this->assertSame($order, $job->order);
        $this->assertEquals($oldStatus, $job->oldStatus);
        $this->assertEquals($newStatus, $job->newStatus);
    }

    #[Test]
    public function handle_method_exists(): void
    {
        // Arrange
        $order = Mockery::mock(Order::class);
        $job = new SendOrderStatusUpdateEmail($order, 'pending', 'confirmed');

        // Assert
        $this->assertTrue(method_exists($job, 'handle'));
    }


    #[Test]
    public function job_implements_should_queue_interface(): void
    {
        // Arrange
        $order = Mockery::mock(Order::class);
        $job = new SendOrderStatusUpdateEmail($order, 'pending', 'confirmed');

        // Assert
        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, $job);
    }

    #[Test]
    public function job_uses_queueable_trait(): void
    {
        // Arrange
        $order = Mockery::mock(Order::class);
        $job = new SendOrderStatusUpdateEmail($order, 'pending', 'confirmed');

        // Assert - Check if the job has methods from Queueable trait
        $this->assertTrue(method_exists($job, 'onQueue'));
        $this->assertTrue(method_exists($job, 'onConnection'));
        $this->assertTrue(method_exists($job, 'delay'));
    }

    #[Test]
    public function job_constructor_accepts_all_required_parameters(): void
    {
        // Arrange
        $order = Mockery::mock(Order::class);
        $oldStatus = 'different_old';
        $newStatus = 'different_new';

        // Act
        $job = new SendOrderStatusUpdateEmail($order, $oldStatus, $newStatus);

        // Assert
        $this->assertSame($order, $job->order);
        $this->assertEquals($oldStatus, $job->oldStatus);
        $this->assertEquals($newStatus, $job->newStatus);
    }
}
