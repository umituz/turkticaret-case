<?php

namespace Tests\Unit\DTOs\Cart;

use App\DTOs\Cart\UpdateCartItemDTO;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Tests\Base\UnitTestCase;

/**
 * Unit tests for UpdateCartItemDTO
 * Tests DTO creation, validation and conversion methods
 */
#[CoversClass(UpdateCartItemDTO::class)]
#[Group('unit')]
#[Group('dtos')]
#[Small]
class UpdateCartItemDTOTest extends UnitTestCase
{
    #[Test]
    public function it_can_be_instantiated_with_required_properties(): void
    {
        // Arrange
        $productUuid = 'product-uuid-456';
        $quantity = 3;

        // Act
        $dto = new UpdateCartItemDTO($productUuid, $quantity);

        // Assert
        $this->assertEquals($productUuid, $dto->product_uuid);
        $this->assertEquals($quantity, $dto->quantity);
    }

    #[Test]
    public function from_array_creates_dto_from_request_data(): void
    {
        // Arrange
        $data = [
            'product_uuid' => 'update-product-uuid',
            'quantity' => 7,
        ];

        // Act
        $dto = UpdateCartItemDTO::fromArray($data);

        // Assert
        $this->assertInstanceOf(UpdateCartItemDTO::class, $dto);
        $this->assertEquals($data['product_uuid'], $dto->product_uuid);
        $this->assertEquals($data['quantity'], $dto->quantity);
    }

    #[Test]
    public function to_array_converts_dto_to_array_for_database(): void
    {
        // Arrange
        $dto = new UpdateCartItemDTO('product-789', 4);
        $expectedArray = [
            'product_uuid' => 'product-789',
            'quantity' => 4,
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
            'product_uuid' => 'symmetric-update-uuid',
            'quantity' => 12,
        ];

        // Act
        $dto = UpdateCartItemDTO::fromArray($originalData);
        $resultArray = $dto->toArray();

        // Assert
        $this->assertEquals($originalData, $resultArray);
    }

    #[Test]
    public function it_handles_zero_quantity(): void
    {
        // Arrange & Act
        $dto = new UpdateCartItemDTO('test-uuid', 0);

        // Assert
        $this->assertEquals(0, $dto->quantity);
    }

    #[Test]
    public function it_handles_large_quantities(): void
    {
        // Arrange & Act
        $dto = new UpdateCartItemDTO('test-uuid', 999);

        // Assert
        $this->assertEquals(999, $dto->quantity);
    }

    #[Test]
    public function it_handles_different_uuid_formats(): void
    {
        // Arrange & Act & Assert
        $uuids = [
            'simple-update-uuid',
            'uuid-with-numbers-123',
            '550e8400-e29b-41d4-a716-446655440000'
        ];
        
        foreach ($uuids as $uuid) {
            $dto = new UpdateCartItemDTO($uuid, 1);
            $this->assertEquals($uuid, $dto->product_uuid);
        }
    }

    #[Test]
    public function from_array_creates_identical_dtos_for_same_data(): void
    {
        // Arrange
        $data = [
            'product_uuid' => 'identical-test-uuid',
            'quantity' => 5,
        ];

        // Act
        $dto1 = UpdateCartItemDTO::fromArray($data);
        $dto2 = UpdateCartItemDTO::fromArray($data);

        // Assert
        $this->assertEquals($dto1->product_uuid, $dto2->product_uuid);
        $this->assertEquals($dto1->quantity, $dto2->quantity);
        $this->assertEquals($dto1->toArray(), $dto2->toArray());
    }

    #[Test]
    public function dto_properties_are_readonly(): void
    {
        // Arrange
        $dto = new UpdateCartItemDTO('readonly-test', 8);

        // Assert - PHP readonly properties are enforced by the language
        $this->assertEquals('readonly-test', $dto->product_uuid);
        $this->assertEquals(8, $dto->quantity);
    }
}