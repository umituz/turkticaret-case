<?php

namespace Tests\Unit\Mail;

use App\Mail\Order\OrderStatusUpdateMail;
use App\Models\Order\Order;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use Tests\Base\UnitTestCase;

/**
 * Unit tests for OrderStatusUpdateMail
 * Tests mail structure, content and methods
 */
#[CoversClass(OrderStatusUpdateMail::class)]
#[Group('unit')]
#[Group('mail')]
#[Small]
class OrderStatusUpdateMailTest extends UnitTestCase
{
    #[Test]
    public function it_can_be_instantiated_with_required_properties(): void
    {
        // Arrange
        $order = Mockery::mock(Order::class);
        $oldStatus = 'pending';
        $newStatus = 'confirmed';

        // Act
        $mail = new OrderStatusUpdateMail($order, $oldStatus, $newStatus);

        // Assert
        $this->assertSame($order, $mail->order);
        $this->assertEquals($oldStatus, $mail->oldStatus);
        $this->assertEquals($newStatus, $mail->newStatus);
    }

    #[Test]
    public function envelope_method_exists(): void
    {
        // Arrange
        $order = Mockery::mock(Order::class);
        $mail = new OrderStatusUpdateMail($order, 'pending', 'confirmed');

        // Assert
        $this->assertTrue(method_exists($mail, 'envelope'));
        $this->assertInstanceOf(Envelope::class, $mail->envelope());
    }

    #[Test]
    public function content_method_exists(): void
    {
        // Arrange
        $order = Mockery::mock(Order::class);
        $mail = new OrderStatusUpdateMail($order, 'pending', 'shipped');

        // Assert
        $this->assertTrue(method_exists($mail, 'content'));
        $this->assertInstanceOf(Content::class, $mail->content());
    }

    #[Test]
    public function attachments_method_exists(): void
    {
        // Arrange
        $order = Mockery::mock(Order::class);
        $mail = new OrderStatusUpdateMail($order, 'pending', 'confirmed');

        // Assert
        $this->assertTrue(method_exists($mail, 'attachments'));
        $this->assertIsArray($mail->attachments());
    }

    #[Test]
    public function mail_is_instance_of_mailable(): void
    {
        // Arrange
        $order = Mockery::mock(Order::class);
        $mail = new OrderStatusUpdateMail($order, 'pending', 'confirmed');

        // Assert
        $this->assertInstanceOf(\Illuminate\Mail\Mailable::class, $mail);
    }


    #[Test]
    public function mail_uses_correct_traits(): void
    {
        // Arrange
        $order = Mockery::mock(Order::class);
        $mail = new OrderStatusUpdateMail($order, 'pending', 'confirmed');

        // Assert - Check if the mail has methods from required traits
        $this->assertTrue(method_exists($mail, 'onQueue')); // From Queueable
        $this->assertTrue(method_exists($mail, 'onConnection')); // From Queueable
    }
}
