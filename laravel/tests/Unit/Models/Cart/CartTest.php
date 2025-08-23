<?php

namespace Tests\Unit\Models\Cart;

use App\Models\Base\BaseUuidModel;
use App\Models\Cart\Cart;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Base\BaseModelUnitTest;

#[CoversClass(Cart::class)]
class CartTest extends BaseModelUnitTest
{
    protected function getModelClass(): string
    {
        return Cart::class;
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
            'user_uuid',
        ];

        $this->assertHasFillable($expectedFillable);
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
    public function it_has_cart_items_relationship_method(): void
    {
        $this->assertHasRelationshipMethod('cartItems');
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
    public function it_can_access_cart_items_relationship(): void
    {
        $cartItemsRelation = $this->model->cartItems();

        $this->assertEquals('App\Models\Cart\CartItem', $cartItemsRelation->getRelated()::class);
        $this->assertEquals('cart_uuid', $cartItemsRelation->getForeignKeyName());
        $this->assertEquals('uuid', $cartItemsRelation->getLocalKeyName());
    }
}
