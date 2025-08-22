<?php

namespace Tests\Unit\Models\Language;

use App\Models\Base\BaseUuidModel;
use App\Models\Language\Language;
use App\Models\Auth\User;
use Tests\Base\BaseModelUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(Language::class)]
class LanguageTest extends BaseModelUnitTest
{
    protected function getModelClass(): string
    {
        return Language::class;
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
            'native_name',
            'locale',
            'direction',
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
    public function it_has_users_relationship_method(): void
    {
        $this->assertHasRelationshipMethod('users');
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
        
        $this->assertEquals('App\Models\Auth\User', $usersRelation->getRelated()::class);
        $this->assertEquals('language_uuid', $usersRelation->getForeignKeyName());
        $this->assertEquals('uuid', $usersRelation->getLocalKeyName());
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
        $scopedQuery = $this->model->scopeByCode($query, 'en');
        
        $this->assertNotNull($scopedQuery);
    }

    #[Test]
    public function it_uses_uuid_as_route_key(): void
    {
        $this->assertEquals('uuid', $this->model->getRouteKeyName());
    }

    #[Test]
    public function it_checks_if_rtl_direction(): void
    {
        $this->model->direction = 'rtl';
        $this->assertTrue($this->model->isRTL());
        
        $this->model->direction = 'ltr';
        $this->assertFalse($this->model->isRTL());
        
        $this->model->direction = null;
        $this->assertFalse($this->model->isRTL());
    }

    #[Test]
    public function it_checks_if_ltr_direction(): void
    {
        $this->model->direction = 'ltr';
        $this->assertTrue($this->model->isLTR());
        
        $this->model->direction = 'rtl';
        $this->assertFalse($this->model->isLTR());
        
        $this->model->direction = null;
        $this->assertFalse($this->model->isLTR());
    }

    #[Test]
    public function it_handles_empty_direction_for_rtl_check(): void
    {
        $this->model->direction = '';
        $this->assertFalse($this->model->isRTL());
    }

    #[Test]
    public function it_handles_empty_direction_for_ltr_check(): void
    {
        $this->model->direction = '';
        $this->assertFalse($this->model->isLTR());
    }

    #[Test]
    public function it_handles_case_sensitivity_for_direction_check(): void
    {
        $this->model->direction = 'RTL';
        $this->assertFalse($this->model->isRTL());
        
        $this->model->direction = 'LTR';
        $this->assertFalse($this->model->isLTR());
    }
}