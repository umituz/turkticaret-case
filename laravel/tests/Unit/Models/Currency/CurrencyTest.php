<?php

namespace Tests\Unit\Models\Currency;

use App\Models\Base\BaseUuidModel;
use App\Models\Currency\Currency;
use App\Models\Country\Country;
use Tests\Base\BaseModelUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(Currency::class)]
class CurrencyTest extends BaseModelUnitTest
{
    protected function getModelClass(): string
    {
        return Currency::class;
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
            'code',
            'name',
            'symbol',
            'decimals',
            'is_active',
        ];

        $this->assertHasFillable($expectedFillable);
    }

    #[Test]
    public function it_has_correct_casts(): void
    {
        $expectedCasts = [
            'decimals' => 'integer',
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
    public function it_has_factory_method(): void
    {
        $this->assertHasFactory();
    }


    #[Test]
    public function it_has_active_scope(): void
    {
        $query = $this->model->newQuery();
        $scopedQuery = $this->model->scopeActive($query);
        
        $this->assertNotNull($scopedQuery);
    }

    #[Test]
    public function it_has_by_code_scope(): void
    {
        $query = $this->model->newQuery();
        $scopedQuery = $this->model->scopeByCode($query, 'USD');
        
        $this->assertNotNull($scopedQuery);
    }

    #[Test]
    public function it_uses_uuid_as_route_key(): void
    {
        $this->assertEquals('uuid', $this->model->getRouteKeyName());
    }

}