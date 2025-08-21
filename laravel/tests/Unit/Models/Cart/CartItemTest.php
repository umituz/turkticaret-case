<?php

namespace Tests\Unit\Models\Cart;

use App\Models\Base\BaseUuidModel;
use App\Models\Cart\Cart;
use App\Models\Cart\CartItem;
use App\Models\Product\Product;
use Tests\Base\BaseModelUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(CartItem::class)]
class CartItemTest extends BaseModelUnitTest
{
    protected function getModelClass(): string
    {
        return CartItem::class;
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
            'cart_uuid',
            'product_uuid',
            'quantity',
            'unit_price',
        ];

        $this->assertHasFillable($expectedFillable);
    }

    #[Test]
    public function it_has_correct_casts(): void
    {
        $expectedCasts = [
            'quantity' => 'integer',
            'unit_price' => 'integer',
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
    public function it_has_cart_relationship_method(): void
    {
        $this->assertHasRelationshipMethod('cart');
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
    public function it_can_access_cart_relationship(): void
    {
        $cartRelation = $this->model->cart();
        
        $this->assertEquals('App\Models\Cart\Cart', $cartRelation->getRelated()::class);
        $this->assertEquals('cart_uuid', $cartRelation->getForeignKeyName());
        $this->assertEquals('uuid', $cartRelation->getOwnerKeyName());
    }

    #[Test]
    public function it_can_access_product_relationship(): void
    {
        $productRelation = $this->model->product();
        
        $this->assertEquals('App\Models\Product\Product', $productRelation->getRelated()::class);
        $this->assertEquals('product_uuid', $productRelation->getForeignKeyName());
        $this->assertEquals('uuid', $productRelation->getOwnerKeyName());
    }

    #[Test]
    public function it_calculates_total_price_correctly(): void
    {
        $this->model->setAttribute('quantity', 3);
        $this->model->setAttribute('unit_price', 1500);
        
        $totalPrice = $this->model->getTotalPriceAttribute();
        
        $this->assertEquals(4500, $totalPrice);
    }

    #[Test]
    public function it_has_total_price_accessor(): void
    {
        $this->model->quantity = 2;
        $this->model->unit_price = 2500;
        
        $this->assertEquals(5000, $this->model->total_price);
    }
}