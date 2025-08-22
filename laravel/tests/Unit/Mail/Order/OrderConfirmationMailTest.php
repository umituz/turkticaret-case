<?php

namespace Tests\Unit\Mail\Order;

use App\Mail\Order\OrderConfirmationMail;
use App\Models\Auth\User;
use App\Models\Order\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(OrderConfirmationMail::class)]
class OrderConfirmationMailTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_correct_subject_with_order_uuid(): void
    {
        $order = $this->createOrderWithUser();
        $mailable = new OrderConfirmationMail($order);

        $expectedSubject = 'Your Order Has Been Confirmed - #' . strtoupper(substr($order->uuid, 0, 8));
        $mailable->assertHasSubject($expectedSubject);
    }

    #[Test]
    public function it_uses_correct_view(): void
    {
        $order = $this->createOrderWithUser();
        $mailable = new OrderConfirmationMail($order);

        $this->assertEquals('emails.order.confirmation', $mailable->content()->view);
    }

    #[Test]
    public function it_contains_order_details(): void
    {
        $user = User::factory()->create(['name' => 'John Customer']);
        $order = Order::factory()->create([
            'user_uuid' => $user->uuid,
            'total_amount' => 150000,
        ]);
        $order->setRelation('user', $user);

        $mailable = new OrderConfirmationMail($order);

        $mailable->assertSeeInHtml('John Customer');
        $mailable->assertSeeInHtml('Order Confirmation #' . substr($order->uuid, 0, 8));
        $mailable->assertSeeInHtml('Thank you for your order!');
    }

    #[Test]
    public function it_contains_expected_content(): void
    {
        $order = $this->createOrderWithUser();
        $mailable = new OrderConfirmationMail($order);

        $mailable->assertSeeInHtml('Order Confirmation');
        $mailable->assertSeeInHtml('Thank you for your order!');
        $mailable->assertSeeInHtml('preparing it for delivery');
        $mailable->assertSeeInHtml('The TurkTicaret Team');
    }

    #[Test]
    public function it_renders_without_errors(): void
    {
        $order = $this->createOrderWithUser();
        $mailable = new OrderConfirmationMail($order);

        $renderedContent = $mailable->render();

        $this->assertIsString($renderedContent);
        $this->assertNotEmpty($renderedContent);
        $this->assertStringContainsString($order->user->name, $renderedContent);
    }

    #[Test]
    public function it_has_order_property(): void
    {
        $order = $this->createOrderWithUser();
        $mailable = new OrderConfirmationMail($order);

        $this->assertInstanceOf(Order::class, $mailable->order);
        $this->assertEquals($order->id, $mailable->order->id);
        $this->assertEquals($order->uuid, $mailable->order->uuid);
    }

    #[Test]
    public function it_contains_layout_elements(): void
    {
        $order = $this->createOrderWithUser();
        $mailable = new OrderConfirmationMail($order);

        $mailable->assertSeeInHtml('TurkTicaret');
        $mailable->assertSeeInHtml('Your Trusted E-Commerce Platform');
    }

    #[Test]
    public function it_has_correct_envelope_configuration(): void
    {
        $order = $this->createOrderWithUser();
        $mailable = new OrderConfirmationMail($order);

        $envelope = $mailable->envelope();
        $expectedSubject = 'Your Order Has Been Confirmed - #' . strtoupper(substr($order->uuid, 0, 8));
        
        $this->assertEquals($expectedSubject, $envelope->subject);
    }

    #[Test]
    public function it_has_no_attachments(): void
    {
        $order = $this->createOrderWithUser();
        $mailable = new OrderConfirmationMail($order);

        $attachments = $mailable->attachments();
        
        $this->assertIsArray($attachments);
        $this->assertEmpty($attachments);
    }

    #[Test]
    public function it_has_correct_content_configuration(): void
    {
        $order = $this->createOrderWithUser();
        $mailable = new OrderConfirmationMail($order);

        $content = $mailable->content();
        
        $this->assertEquals('emails.order.confirmation', $content->view);
    }

    #[Test]
    public function it_handles_order_with_items(): void
    {
        $user = User::factory()->create(['name' => 'Customer With Items']);
        $order = Order::factory()->create([
            'user_uuid' => $user->uuid,
            'total_amount' => 499800,
        ]);
        $order->setRelation('user', $user);

        // Mock order items
        $orderItems = collect([
            (object) [
                'product_name' => 'Test Product',
                'quantity' => 2,
                'total_price' => 499800,
            ]
        ]);
        $order->setRelation('orderItems', $orderItems);

        $mailable = new OrderConfirmationMail($order);

        $renderedContent = $mailable->render();
        $this->assertStringContainsString('Customer With Items', $renderedContent);
    }

    private function createOrderWithUser(): Order
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'name' => 'Test Customer'
        ]);
        
        $order = Order::factory()->create([
            'user_uuid' => $user->uuid,
            'total_amount' => 25000
        ]);
        
        $order->setRelation('user', $user);
        
        return $order;
    }
}