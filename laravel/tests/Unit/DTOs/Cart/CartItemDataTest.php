<?php

namespace Tests\Unit\DTOs\Cart;

use App\DTOs\Cart\CartItemData;
use App\Models\Cart\CartItem;
use App\Models\Product\Product;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Tests\Base\UnitTestCase;
use Mockery;
use Illuminate\Database\Eloquent\Collection;

/**
 * Unit tests for CartItemData DTO
 * Tests DTO creation, validation and conversion methods
 */
#[CoversClass(CartItemData::class)]
#[Group('unit')]
#[Group('dtos')]
#[Small]
class CartItemDataTest extends UnitTestCase
{
    #[Test]
    public function it_can_be_instantiated_with_all_properties(): void
    {
        // Arrange
        $uuid = 'cart-item-uuid';
        $productUuid = 'product-uuid';
        $productName = 'Test Product';
        $productSku = 'SKU-123';
        $productImagePath = '/images/test.jpg';
        $quantity = 2;
        $unitPrice = 1000;
        $totalPrice = 2000;
        $availableStock = 10;
        $isAvailable = true;

        // Act
        $dto = new CartItemData(
            $uuid,
            $productUuid,
            $productName,
            $productSku,
            $productImagePath,
            $quantity,
            $unitPrice,
            $totalPrice,
            $availableStock,
            $isAvailable
        );

        // Assert
        $this->assertEquals($uuid, $dto->uuid);
        $this->assertEquals($productUuid, $dto->product_uuid);
        $this->assertEquals($productName, $dto->product_name);
        $this->assertEquals($productSku, $dto->product_sku);
        $this->assertEquals($productImagePath, $dto->product_image_path);
        $this->assertEquals($quantity, $dto->quantity);
        $this->assertEquals($unitPrice, $dto->unit_price);
        $this->assertEquals($totalPrice, $dto->total_price);
        $this->assertEquals($availableStock, $dto->available_stock);
        $this->assertEquals($isAvailable, $dto->is_available);
    }

    #[Test]
    public function from_model_method_exists(): void
    {
        // Assert
        $this->assertTrue(method_exists(CartItemData::class, 'fromModel'));
    }

    #[Test]
    public function from_model_is_static_method(): void
    {
        // Assert
        $reflection = new \ReflectionMethod(CartItemData::class, 'fromModel');
        $this->assertTrue($reflection->isStatic());
    }

    #[Test]
    public function from_collection_method_exists(): void
    {
        // Assert
        $this->assertTrue(method_exists(CartItemData::class, 'fromCollection'));
        
        $reflection = new \ReflectionMethod(CartItemData::class, 'fromCollection');
        $this->assertTrue($reflection->isStatic());
    }


    #[Test]
    public function to_array_converts_dto_to_structured_array(): void
    {
        // Arrange
        $dto = new CartItemData(
            'item-uuid',
            'product-uuid',
            'Test Product',
            'TEST-SKU',
            '/images/test.jpg',
            2,
            1500,
            3000,
            10,
            true
        );
        
        $expectedArray = [
            'uuid' => 'item-uuid',
            'product' => [
                'uuid' => 'product-uuid',
                'name' => 'Test Product',
                'sku' => 'TEST-SKU',
                'image_path' => '/images/test.jpg',
                'available_stock' => 10,
                'is_available' => true,
            ],
            'quantity' => 2,
            'unit_price' => 1500,
            'total_price' => 3000,
        ];

        // Act
        $result = $dto->toArray();

        // Assert
        $this->assertEquals($expectedArray, $result);
    }

    #[Test]
    public function get_formatted_unit_price_divides_by_hundred(): void
    {
        // Arrange
        $dto = new CartItemData('', '', '', '', null, 1, 1299, 1299, 10, true);

        // Act
        $formattedPrice = $dto->getFormattedUnitPrice();

        // Assert
        $this->assertEquals(12.99, $formattedPrice);
    }

    #[Test]
    public function get_formatted_total_price_divides_by_hundred(): void
    {
        // Arrange
        $dto = new CartItemData('', '', '', '', null, 2, 1299, 2598, 10, true);

        // Act
        $formattedPrice = $dto->getFormattedTotalPrice();

        // Assert
        $this->assertEquals(25.98, $formattedPrice);
    }

    #[Test]
    public function has_stock_issue_returns_true_when_quantity_exceeds_stock(): void
    {
        // Arrange
        $dto = new CartItemData('', '', '', '', null, 15, 1000, 15000, 10, true);

        // Act & Assert
        $this->assertTrue($dto->hasStockIssue());
    }

    #[Test]
    public function has_stock_issue_returns_false_when_quantity_within_stock(): void
    {
        // Arrange
        $dto = new CartItemData('', '', '', '', null, 5, 1000, 5000, 10, true);

        // Act & Assert
        $this->assertFalse($dto->hasStockIssue());
    }

    #[Test]
    public function has_stock_issue_returns_false_when_quantity_equals_stock(): void
    {
        // Arrange
        $dto = new CartItemData('', '', '', '', null, 10, 1000, 10000, 10, true);

        // Act & Assert
        $this->assertFalse($dto->hasStockIssue());
    }

    #[Test]
    public function is_product_available_returns_availability_status(): void
    {
        // Arrange
        $availableDto = new CartItemData('', '', '', '', null, 1, 1000, 1000, 10, true);
        $unavailableDto = new CartItemData('', '', '', '', null, 1, 1000, 1000, 10, false);

        // Act & Assert
        $this->assertTrue($availableDto->isProductAvailable());
        $this->assertFalse($unavailableDto->isProductAvailable());
    }

    #[Test]
    public function handles_null_product_image_path(): void
    {
        // Arrange & Act
        $dto = new CartItemData(
            'item-uuid',
            'product-uuid',
            'No Image Product',
            'NO-IMG-SKU',
            null, // null image path
            1,
            1000,
            1000,
            5,
            true
        );

        // Assert
        $this->assertNull($dto->product_image_path);
        $array = $dto->toArray();
        $this->assertNull($array['product']['image_path']);
    }
}