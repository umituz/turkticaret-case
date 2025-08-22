<?php

namespace Tests\Unit\Exceptions\Product;

use App\Exceptions\Product\OutOfStockException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(OutOfStockException::class)]
class OutOfStockExceptionTest extends TestCase
{
    #[Test]
    public function it_has_correct_default_message_and_code(): void
    {
        $exception = new OutOfStockException();
        
        $this->assertEquals('Product is out of stock', $exception->getMessage());
        $this->assertEquals(422, $exception->getCode());
    }

    #[Test]
    public function it_creates_detailed_message_with_product_name(): void
    {
        $exception = new OutOfStockException('iPhone 15 Pro');
        
        $expectedMessage = "Product 'iPhone 15 Pro' is out of stock";
        $this->assertEquals($expectedMessage, $exception->getMessage());
        $this->assertEquals(422, $exception->getCode());
    }

    #[Test]
    public function it_handles_null_product_name(): void
    {
        $exception = new OutOfStockException(null);
        
        $this->assertEquals('Product is out of stock', $exception->getMessage());
    }

    #[Test]
    public function it_handles_empty_product_name(): void
    {
        $exception = new OutOfStockException('');
        
        $this->assertEquals('Product is out of stock', $exception->getMessage());
    }

    #[Test]
    public function it_extends_base_exception(): void
    {
        $exception = new OutOfStockException();
        
        $this->assertInstanceOf(\Exception::class, $exception);
    }
}