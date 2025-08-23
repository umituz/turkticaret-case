<?php

namespace Tests\Unit\Models\Country;

use App\Models\Base\BaseUuidModel;
use App\Models\Country\Country;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Base\BaseModelUnitTest;

#[CoversClass(Country::class)]
class CountryTest extends BaseModelUnitTest
{
    protected function getModelClass(): string
    {
        return Country::class;
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
            'locale',
            'currency_uuid',
            'is_active',
        ];

        $this->assertHasFillable($expectedFillable);
    }

    #[Test]
    public function it_has_correct_casts(): void
    {
        $expectedCasts = [
            'is_active' => 'boolean',
        ];

        $this->assertHasCasts($expectedCasts);
    }

    #[Test]
    public function it_uses_uuid_primary_key(): void
    {
        $this->assertUsesUuidPrimaryKey();
    }

    #[Test]
    public function it_uses_timestamps(): void
    {
        $this->assertUsesTimestamps();
    }

    #[Test]
    public function it_has_users_relationship_method(): void
    {
        $this->assertHasRelationshipMethod('users');
    }

    #[Test]
    public function it_has_currencies_relationship_method(): void
    {
        $this->assertHasRelationshipMethod('currencies');
    }

    #[Test]
    public function it_has_factory_method(): void
    {
        $this->assertHasFactory();
    }

    #[Test]
    public function it_can_access_users_relationship(): void
    {
        $usersRelation = $this->model->users();

        $this->assertEquals('App\Models\User\User', $usersRelation->getRelated()::class);
        $this->assertEquals('country_code', $usersRelation->getForeignKeyName());
        $this->assertEquals('code', $usersRelation->getLocalKeyName());
    }

    #[Test]
    public function it_can_access_currencies_relationship(): void
    {
        $currenciesRelation = $this->model->currencies();

        $this->assertEquals('App\Models\Currency\Currency', $currenciesRelation->getRelated()::class);
        $this->assertEquals('country_uuid', $currenciesRelation->getForeignKeyName());
        $this->assertEquals('uuid', $currenciesRelation->getLocalKeyName());
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
        $scopedQuery = $this->model->scopeByCode($query, 'US');

        $this->assertNotNull($scopedQuery);
    }

    #[Test]
    public function it_uses_uuid_as_route_key(): void
    {
        $this->assertEquals('uuid', $this->model->getRouteKeyName());
    }

    #[Test]
    public function it_gets_default_currency(): void
    {
        $defaultCurrency = $this->model->getDefaultCurrency();

        $this->assertNull($defaultCurrency);
    }
}
