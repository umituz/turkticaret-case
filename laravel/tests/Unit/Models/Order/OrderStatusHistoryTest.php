<?php

namespace Tests\Unit\Models\Order;

use App\Models\Order\OrderStatusHistory;
use App\Models\Order\Order;
use App\Models\User\User;
use App\Enums\Order\OrderStatusEnum;
use Tests\Base\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;

/**
 * Unit tests for OrderStatusHistory Model
 * Tests model attributes, casts, and relationships
 */
#[CoversClass(OrderStatusHistory::class)]
#[Group('unit')]
#[Group('models')]
#[Small]
class OrderStatusHistoryTest extends UnitTestCase
{
    #[Test]
    public function model_has_correct_fillable_attributes(): void
    {
        // Arrange & Act
        $history = new OrderStatusHistory();
        
        // Assert
        $expectedFillable = [
            'order_uuid',
            'old_status',
            'new_status',
            'changed_by_uuid',
            'notes',
        ];
        
        $this->assertEquals($expectedFillable, $history->getFillable());
    }

    #[Test]
    public function model_has_correct_casts(): void
    {
        // Arrange & Act
        $history = new OrderStatusHistory();
        $casts = $history->getCasts();
        
        // Assert - Check enum casts
        $this->assertEquals(OrderStatusEnum::class, $casts['old_status']);
        $this->assertEquals(OrderStatusEnum::class, $casts['new_status']);
        
        // Verify we have the base datetime casts
        $this->assertEquals('datetime', $casts['deleted_at']); // From SoftDeletes
        
        // Verify cast count
        $this->assertGreaterThanOrEqual(3, count($casts));
    }

    #[Test]
    public function model_extends_base_uuid_model(): void
    {
        // Arrange & Act
        $history = new OrderStatusHistory();
        
        // Assert
        $this->assertInstanceOf(\App\Models\Base\BaseUuidModel::class, $history);
    }

    #[Test]
    public function model_uses_correct_table_name(): void
    {
        // Arrange & Act
        $history = new OrderStatusHistory();
        
        // Assert
        $this->assertEquals('order_status_histories', $history->getTable());
    }

    #[Test]
    public function order_status_history_can_be_created_with_all_attributes(): void
    {
        // Arrange
        $attributes = [
            'order_uuid' => 'test-order-uuid',
            'old_status' => OrderStatusEnum::PENDING,
            'new_status' => OrderStatusEnum::CONFIRMED,
            'changed_by_uuid' => 'test-user-uuid',
            'notes' => 'Status changed due to payment confirmation',
        ];

        // Act
        $history = new OrderStatusHistory($attributes);

        // Assert
        $this->assertEquals('test-order-uuid', $history->order_uuid);
        $this->assertEquals(OrderStatusEnum::PENDING, $history->old_status);
        $this->assertEquals(OrderStatusEnum::CONFIRMED, $history->new_status);
        $this->assertEquals('test-user-uuid', $history->changed_by_uuid);
        $this->assertEquals('Status changed due to payment confirmation', $history->notes);
    }

    #[Test]
    public function model_has_order_relationship(): void
    {
        // Arrange & Act
        $history = new OrderStatusHistory();
        $relationship = $history->order();
        
        // Assert
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relationship);
        $this->assertEquals('order_uuid', $relationship->getForeignKeyName());
        $this->assertEquals('uuid', $relationship->getOwnerKeyName());
    }

    #[Test]
    public function model_has_changed_by_user_relationship(): void
    {
        // Arrange & Act
        $history = new OrderStatusHistory();
        $relationship = $history->changedBy();
        
        // Assert
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relationship);
        $this->assertEquals('changed_by_uuid', $relationship->getForeignKeyName());
        $this->assertEquals('uuid', $relationship->getOwnerKeyName());
    }

    #[Test]
    public function enum_casts_work_correctly_for_status_fields(): void
    {
        // Arrange
        $history = new OrderStatusHistory([
            'order_uuid' => 'test-order',
            'old_status' => 'pending',  // String will be cast to enum
            'new_status' => 'confirmed', // String will be cast to enum
            'changed_by_uuid' => 'test-user',
            'notes' => 'Test status change',
        ]);

        // Act & Assert - Verify the casts work
        $this->assertInstanceOf(OrderStatusEnum::class, $history->old_status);
        $this->assertInstanceOf(OrderStatusEnum::class, $history->new_status);
        $this->assertEquals(OrderStatusEnum::PENDING, $history->old_status);
        $this->assertEquals(OrderStatusEnum::CONFIRMED, $history->new_status);
    }

    #[Test]
    public function model_handles_null_values_for_optional_fields(): void
    {
        // Arrange
        $history = new OrderStatusHistory([
            'order_uuid' => 'test-order',
            'old_status' => OrderStatusEnum::PENDING,
            'new_status' => OrderStatusEnum::CONFIRMED,
            'changed_by_uuid' => null, // Optional - system change
            'notes' => null, // Optional
        ]);

        // Act & Assert
        $this->assertEquals('test-order', $history->order_uuid);
        $this->assertEquals(OrderStatusEnum::PENDING, $history->old_status);
        $this->assertEquals(OrderStatusEnum::CONFIRMED, $history->new_status);
        $this->assertNull($history->changed_by_uuid);
        $this->assertNull($history->notes);
    }

    #[Test]
    public function model_tracks_complete_status_progression(): void
    {
        // Arrange
        $statusProgression = [
            ['old' => null, 'new' => OrderStatusEnum::PENDING],
            ['old' => OrderStatusEnum::PENDING, 'new' => OrderStatusEnum::CONFIRMED],
            ['old' => OrderStatusEnum::CONFIRMED, 'new' => OrderStatusEnum::PROCESSING],
            ['old' => OrderStatusEnum::PROCESSING, 'new' => OrderStatusEnum::SHIPPED],
            ['old' => OrderStatusEnum::SHIPPED, 'new' => OrderStatusEnum::DELIVERED],
        ];

        // Act & Assert
        foreach ($statusProgression as $index => $transition) {
            $history = new OrderStatusHistory([
                'order_uuid' => 'test-order',
                'old_status' => $transition['old'],
                'new_status' => $transition['new'],
                'changed_by_uuid' => 'test-user',
                'notes' => "Transition step {$index}",
            ]);

            $this->assertEquals($transition['old'], $history->old_status);
            $this->assertEquals($transition['new'], $history->new_status);
            $this->assertInstanceOf(OrderStatusEnum::class, $history->new_status);
            
            if ($transition['old'] !== null) {
                $this->assertInstanceOf(OrderStatusEnum::class, $history->old_status);
            }
        }
    }
}