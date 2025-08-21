<?php

namespace Tests\Unit\Models\Category;

use App\Models\Base\BaseUuidModel;
use App\Models\Category\Category;
use App\Models\Product\Product;
use Tests\Base\BaseModelUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(Category::class)]
class CategoryTest extends BaseModelUnitTest
{
    protected function getModelClass(): string
    {
        return Category::class;
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
            'is_active',
        ];

        $this->assertHasFillable($expectedFillable);
    }

    #[Test]
    public function it_has_correct_casts(): void
    {
        $expectedCasts = [
            'is_active' => 'boolean',
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
    public function it_has_products_relationship_method(): void
    {
        $this->assertHasRelationshipMethod('products');
    }

    #[Test]
    public function it_has_factory_method(): void
    {
        $this->assertHasFactory();
    }

    #[Test]
    public function it_can_access_products_relationship(): void
    {
        $productsRelation = $this->model->products();
        
        $this->assertEquals('App\Models\Product\Product', $productsRelation->getRelated()::class);
        $this->assertEquals('category_uuid', $productsRelation->getForeignKeyName());
        $this->assertEquals('uuid', $productsRelation->getLocalKeyName());
    }
}