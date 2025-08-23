<?php

namespace Tests\Unit\Models\Order;

use App\Models\Base\BaseUuidModel;
use App\Models\Order\Order;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Base\BaseModelUnitTest;

#[CoversClass(Order::class)]
class OrderTest extends BaseModelUnitTest
{
    protected function getModelClass(): string
    {
        return Order::class;
    }

    #[Test]
    public function it_extends_base_uuid_model(): void
    {
        $this->assertExtendsBaseClass(BaseUuidModel::class);
    }

    #[Test]
    public function it_has_correct_fillable_attributes(): void
    {
        $expectedFillable = [
            'order_number',
            'user_uuid',
            'status',
            'total_amount',
            'shipping_address',
            'notes',
            'shipped_at',
            'delivered_at',
        ];

        $this->assertHasFillable($expectedFillable);
    }

    #[Test]
    public function it_has_correct_casts(): void
    {
        $expectedCasts = [
            'total_amount' => 'integer',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];

        $this->assertHasCasts($expectedCasts);
    }

    #[Test]
    public function it_uses_uuid_primary_key(): void
    {
        $this->assertUsesUuidPrimaryKey();
    }

    #[Test]
    public function it_uses_soft_deletes(): void
    {
        $this->assertUsesSoftDeletes();
    }

    #[Test]
    public function it_uses_timestamps(): void
    {
        $this->assertUsesTimestamps();
    }

    #[Test]
    public function it_has_user_relationship_method(): void
    {
        $this->assertHasRelationshipMethod('user');
    }

    #[Test]
    public function it_has_order_items_relationship_method(): void
    {
        $this->assertHasRelationshipMethod('orderItems');
    }

    #[Test]
    public function it_has_factory_method(): void
    {
        $this->assertHasFactory();
    }

    #[Test]
    public function it_can_access_user_relationship(): void
    {
        $userRelation = $this->model->user();

        $this->assertEquals('App\Models\User\User', $userRelation->getRelated()::class);
        $this->assertEquals('user_uuid', $userRelation->getForeignKeyName());
        $this->assertEquals('uuid', $userRelation->getOwnerKeyName());
    }

    #[Test]
    public function it_can_access_order_items_relationship(): void
    {
        $orderItemsRelation = $this->model->orderItems();

        $this->assertEquals('App\Models\Order\OrderItem', $orderItemsRelation->getRelated()::class);
        $this->assertEquals('order_uuid', $orderItemsRelation->getForeignKeyName());
        $this->assertEquals('uuid', $orderItemsRelation->getLocalKeyName());
    }
}
