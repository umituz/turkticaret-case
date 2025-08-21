<?php

namespace Tests\Unit\Models\Authority;

use App\Models\Authority\Permission;
use Tests\Base\BaseAuthorityModelUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission as SpatiePermission;

#[CoversClass(Permission::class)]
class PermissionTest extends BaseAuthorityModelUnitTest
{
    protected function getModelClass(): string
    {
        return Permission::class;
    }

    #[Test]
    public function it_extends_spatie_permission(): void
    {
        $this->assertInstanceOf(SpatiePermission::class, $this->model);
    }

    #[Test]
    public function it_uses_expected_traits(): void
    {
        $this->assertHasTraits([
            'Illuminate\Database\Eloquent\Concerns\HasUuids',
            'Illuminate\Database\Eloquent\SoftDeletes',
        ]);
    }

    #[Test]
    public function it_has_uuid_primary_key(): void
    {
        $this->assertUsesUuidPrimaryKey();
    }

    #[Test]
    public function it_uses_soft_deletes(): void
    {
        $this->assertUsesSoftDeletes();
    }

    #[Test]
    public function it_has_correct_fillable_attributes(): void
    {
        $expectedFillable = [
            'name',
            'guard_name',
            'description',
            'group',
        ];

        $this->assertHasFillable($expectedFillable);
    }

    #[Test]
    public function it_has_correct_casts(): void
    {
        $expectedCasts = [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];

        $this->assertHasCasts($expectedCasts);
    }

    #[Test]
    public function it_uses_timestamps(): void
    {
        $this->assertUsesTimestamps();
    }

    #[Test]
    public function it_has_correct_route_key_name(): void
    {
        $this->assertEquals('uuid', $this->model->getRouteKeyName());
    }

    #[Test]
    public function it_has_correct_primary_key_configuration(): void
    {
        $this->assertEquals('uuid', $this->model->getKeyName());
        $this->assertFalse($this->model->getIncrementing());
        $this->assertEquals('string', $this->model->getKeyType());
    }
}