<?php

namespace Tests\Unit\Jobs\Order;

use App\Jobs\Order\SendOrderConfirmationJob;
use App\Mail\Order\OrderConfirmationMail;
use App\Models\Auth\User;
use App\Models\Order\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(SendOrderConfirmationJob::class)]
class SendOrderConfirmationJobTest extends TestCase
{
    use RefreshDatabase;


    #[Test]
    public function it_can_be_dispatched(): void
    {
        Queue::fake();
        
        $order = $this->createOrderWithUser();
        
        SendOrderConfirmationJob::dispatch($order);
        
        Queue::assertPushed(SendOrderConfirmationJob::class, function ($job) use ($order) {
            return $job->order->id === $order->id;
        });
    }

    #[Test]
    public function it_sends_confirmation_email_to_user(): void
    {
        Mail::fake();
        
        $order = $this->createOrderWithUser();
        
        $job = new SendOrderConfirmationJob($order);
        $job->handle();
        
        Mail::assertSent(OrderConfirmationMail::class, function ($mail) use ($order) {
            return $mail->order->id === $order->id &&
                   $mail->hasTo($order->user->email);
        });
    }

    #[Test]
    public function it_loads_order_with_relationships(): void
    {
        Mail::fake();
        
        $order = $this->createOrderWithUser();
        $job = new SendOrderConfirmationJob($order);
        
        // Mock the order loading behavior
        $this->assertInstanceOf(Order::class, $job->order);
        
        $job->handle();
        
        Mail::assertSentCount(1);
    }

    #[Test]
    public function it_handles_execution_correctly(): void
    {
        Mail::fake();
        
        $order = $this->createOrderWithUser();
        $job = new SendOrderConfirmationJob($order);
        
        // Should not throw any exceptions
        $this->assertNull($job->handle());
        
        Mail::assertSentCount(1);
    }

    #[Test]
    public function it_has_correct_queue_interactions(): void
    {
        $order = $this->createOrderWithUser();
        $job = (new SendOrderConfirmationJob($order))->withFakeQueueInteractions();
        
        Mail::fake();
        $job->handle();
        
        $job->assertNotReleased();
        $job->assertNotDeleted();
        $job->assertNotFailed();
    }

    #[Test]
    public function it_can_be_dispatched_to_queue(): void
    {
        Queue::fake();
        
        $order = $this->createOrderWithUser();
        
        SendOrderConfirmationJob::dispatch($order);
        
        Queue::assertPushed(SendOrderConfirmationJob::class);
    }

    #[Test]
    public function it_works_with_order_that_has_user_relationship(): void
    {
        Mail::fake();
        
        $user = User::factory()->create([
            'email' => 'customer@example.com',
            'name' => 'Test Customer'
        ]);
        
        $order = Order::factory()->create([
            'user_uuid' => $user->uuid,
            'total_amount' => 50000
        ]);
        
        $order->setRelation('user', $user);
        
        $job = new SendOrderConfirmationJob($order);
        $job->handle();
        
        Mail::assertSent(OrderConfirmationMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    private function createOrderWithUser(): Order
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'name' => 'Test User'
        ]);
        
        $order = Order::factory()->create([
            'user_uuid' => $user->uuid,
            'total_amount' => 25000
        ]);
        
        $order->setRelation('user', $user);
        
        return $order;
    }
}