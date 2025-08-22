<?php

namespace Tests\Unit\Exceptions\Product;

use App\Exceptions\Product\InsufficientStockException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(InsufficientStockException::class)]
class InsufficientStockExceptionTest extends TestCase
{
    #[Test]
    public function it_has_correct_default_message_and_code(): void
    {
        $exception = new InsufficientStockException();
        
        $this->assertEquals('Insufficient stock for requested quantity', $exception->getMessage());
        $this->assertEquals(422, $exception->getCode());
    }

    #[Test]
    public function it_creates_detailed_message_with_product_info(): void
    {
        $exception = new InsufficientStockException('iPhone 15 Pro', 10, 5);
        
        $expectedMessage = "Insufficient stock for product 'iPhone 15 Pro'. Requested: 10, Available: 5";
        $this->assertEquals($expectedMessage, $exception->getMessage());
        $this->assertEquals(422, $exception->getCode());
    }

    #[Test]
    public function it_handles_null_product_name(): void
    {
        $exception = new InsufficientStockException(null, 10, 5);
        
        $this->assertEquals('Insufficient stock for requested quantity', $exception->getMessage());
    }

    #[Test]
    public function it_handles_null_quantities(): void
    {
        $exception = new InsufficientStockException('Test Product', null, null);
        
        $this->assertEquals('Insufficient stock for requested quantity', $exception->getMessage());
    }

    #[Test]
    public function it_extends_base_exception(): void
    {
        $exception = new InsufficientStockException();
        
        $this->assertInstanceOf(\Exception::class, $exception);
    }
}