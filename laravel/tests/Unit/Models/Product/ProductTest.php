<?php

namespace Tests\Unit\Models\Product;

use App\Models\Base\BaseUuidModel;
use App\Models\Product\Product;
use App\Models\Category\Category;
use App\Exceptions\Product\InsufficientStockException;
use App\Exceptions\Product\OutOfStockException;
use Tests\Base\BaseModelUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(Product::class)]
class ProductTest extends BaseModelUnitTest
{
    protected function getModelClass(): string
    {
        return Product::class;
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
            'name',
            'description',
            'slug',
            'sku',
            'price',
            'stock_quantity',
            'is_active',
            'is_featured',
            'category_uuid',
        ];

        $this->assertHasFillable($expectedFillable);
    }

    #[Test]
    public function it_has_correct_casts(): void
    {
        $expectedCasts = [
            'price' => 'integer',
            'stock_quantity' => 'integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
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
    public function it_has_category_relationship_method(): void
    {
        $this->assertHasRelationshipMethod('category');
    }

    #[Test]
    public function it_has_cart_items_relationship_method(): void
    {
        $this->assertHasRelationshipMethod('cartItems');
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
    public function it_can_access_category_relationship(): void
    {
        $categoryRelation = $this->model->category();
        
        $this->assertEquals('App\Models\Category\Category', $categoryRelation->getRelated()::class);
        $this->assertEquals('category_uuid', $categoryRelation->getForeignKeyName());
        $this->assertEquals('uuid', $categoryRelation->getOwnerKeyName());
    }

    #[Test]
    public function it_can_access_cart_items_relationship(): void
    {
        $cartItemsRelation = $this->model->cartItems();
        
        $this->assertEquals('App\Models\Cart\CartItem', $cartItemsRelation->getRelated()::class);
        $this->assertEquals('product_uuid', $cartItemsRelation->getForeignKeyName());
        $this->assertEquals('uuid', $cartItemsRelation->getLocalKeyName());
    }

    #[Test]
    public function it_can_access_order_items_relationship(): void
    {
        $orderItemsRelation = $this->model->orderItems();
        
        $this->assertEquals('App\Models\Order\OrderItem', $orderItemsRelation->getRelated()::class);
        $this->assertEquals('product_uuid', $orderItemsRelation->getForeignKeyName());
        $this->assertEquals('uuid', $orderItemsRelation->getLocalKeyName());
    }

    #[Test]
    public function it_can_check_if_product_has_sufficient_stock(): void
    {
        $product = new Product(['stock_quantity' => 10]);
        
        $this->assertTrue($product->hasStock(5));
        $this->assertTrue($product->hasStock(10));
        $this->assertFalse($product->hasStock(15));
    }

    #[Test]
    public function it_can_check_if_product_has_any_stock(): void
    {
        $productWithStock = new Product(['stock_quantity' => 5]);
        $productOutOfStock = new Product(['stock_quantity' => 0]);
        
        $this->assertTrue($productWithStock->isInStock());
        $this->assertFalse($productOutOfStock->isInStock());
    }


}