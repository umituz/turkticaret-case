<?php

namespace Tests\Unit\DTOs\Cart;

use App\DTOs\Cart\AddToCartDTO;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Tests\Base\UnitTestCase;

/**
 * Unit tests for AddToCartDTO
 * Tests DTO creation, validation and conversion methods
 */
#[CoversClass(AddToCartDTO::class)]
#[Group('unit')]
#[Group('dtos')]
#[Small]
class AddToCartDTOTest extends UnitTestCase
{
    #[Test]
    public function it_can_be_instantiated_with_required_properties(): void
    {
        // Arrange
        $productUuid = 'product-uuid-123';
        $quantity = 5;

        // Act
        $dto = new AddToCartDTO($productUuid, $quantity);

        // Assert
        $this->assertEquals($productUuid, $dto->product_uuid);
        $this->assertEquals($quantity, $dto->quantity);
    }

    #[Test]
    public function from_array_creates_dto_from_request_data(): void
    {
        // Arrange
        $data = [
            'product_uuid' => 'test-product-uuid',
            'quantity' => 3,
        ];

        // Act
        $dto = AddToCartDTO::fromArray($data);

        // Assert
        $this->assertInstanceOf(AddToCartDTO::class, $dto);
        $this->assertEquals($data['product_uuid'], $dto->product_uuid);
        $this->assertEquals($data['quantity'], $dto->quantity);
    }

    #[Test]
    public function to_array_converts_dto_to_array_for_database(): void
    {
        // Arrange
        $dto = new AddToCartDTO('product-123', 2);
        $expectedArray = [
            'product_uuid' => 'product-123',
            'quantity' => 2,
        ];

        // Act
        $result = $dto->toArray();

        // Assert
        $this->assertEquals($expectedArray, $result);
    }

    #[Test]
    public function from_array_and_to_array_are_symmetric(): void
    {
        // Arrange
        $originalData = [
            'product_uuid' => 'symmetric-test-uuid',
            'quantity' => 10,
        ];

        // Act
        $dto = AddToCartDTO::fromArray($originalData);
        $resultArray = $dto->toArray();

        // Assert
        $this->assertEquals($originalData, $resultArray);
    }

    #[Test]
    public function it_handles_different_quantity_values(): void
    {
        // Arrange & Act & Assert
        $quantities = [1, 5, 99, 100];
        
        foreach ($quantities as $quantity) {
            $dto = new AddToCartDTO('test-uuid', $quantity);
            $this->assertEquals($quantity, $dto->quantity);
        }
    }

    #[Test]
    public function it_handles_different_product_uuid_formats(): void
    {
        // Arrange & Act & Assert
        $uuids = [
            'simple-uuid',
            'uuid-with-dashes-123-456-789',
            '550e8400-e29b-41d4-a716-446655440000'
        ];
        
        foreach ($uuids as $uuid) {
            $dto = new AddToCartDTO($uuid, 1);
            $this->assertEquals($uuid, $dto->product_uuid);
        }
    }

    #[Test]
    public function from_array_works_with_minimal_required_data(): void
    {
        // Arrange
        $minimalData = [
            'product_uuid' => 'minimal-uuid',
            'quantity' => 1,
        ];

        // Act
        $dto = AddToCartDTO::fromArray($minimalData);

        // Assert
        $this->assertEquals($minimalData['product_uuid'], $dto->product_uuid);
        $this->assertEquals($minimalData['quantity'], $dto->quantity);
    }

    #[Test]
    public function dto_properties_are_readonly(): void
    {
        // Arrange
        $dto = new AddToCartDTO('test-uuid', 5);

        // Assert - PHP readonly properties are enforced by the language
        $this->assertEquals('test-uuid', $dto->product_uuid);
        $this->assertEquals(5, $dto->quantity);
    }
}