<?php

namespace Tests\Unit\Models\Order;

use App\Models\Base\BaseUuidModel;
use App\Models\Order\Order;
use App\Models\Order\OrderItem;
use App\Models\Product\Product;
use Tests\Base\BaseModelUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(OrderItem::class)]
class OrderItemTest extends BaseModelUnitTest
{
    protected function getModelClass(): string
    {
        return OrderItem::class;
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
            'order_uuid',
            'product_uuid',
            'product_name',
            'quantity',
            'unit_price',
            'total_price',
        ];

        $this->assertHasFillable($expectedFillable);
    }

    #[Test]
    public function it_has_correct_casts(): void
    {
        $expectedCasts = [
            'quantity' => 'integer',
            'unit_price' => 'integer',
            'total_price' => 'integer',
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
    public function it_has_order_relationship_method(): void
    {
        $this->assertHasRelationshipMethod('order');
    }

    #[Test]
    public function it_has_product_relationship_method(): void
    {
        $this->assertHasRelationshipMethod('product');
    }

    #[Test]
    public function it_has_factory_method(): void
    {
        $this->assertHasFactory();
    }

    #[Test]
    public function it_can_access_order_relationship(): void
    {
        $orderRelation = $this->model->order();
        
        $this->assertEquals('App\Models\Order\Order', $orderRelation->getRelated()::class);
        $this->assertEquals('order_uuid', $orderRelation->getForeignKeyName());
        $this->assertEquals('uuid', $orderRelation->getOwnerKeyName());
    }

    #[Test]
    public function it_can_access_product_relationship(): void
    {
        $productRelation = $this->model->product();
        
        $this->assertEquals('App\Models\Product\Product', $productRelation->getRelated()::class);
        $this->assertEquals('product_uuid', $productRelation->getForeignKeyName());
        $this->assertEquals('uuid', $productRelation->getOwnerKeyName());
    }
}