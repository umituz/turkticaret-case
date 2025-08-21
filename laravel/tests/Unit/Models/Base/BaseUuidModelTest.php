<?php

namespace Tests\Unit\Models\Base;

use App\Models\Base\BaseUuidModel;
use Tests\Base\BaseModelUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Database\Eloquent\Model;

// Create a concrete implementation for testing
class TestUuidModel extends BaseUuidModel
{
    protected $table = 'test_models';
    protected $fillable = ['name', 'description'];
}

#[CoversClass(BaseUuidModel::class)]
class BaseUuidModelTest extends BaseModelUnitTest
{
    protected function getModelClass(): string
    {
        return TestUuidModel::class;
    }

    #[Test]
    public function it_extends_eloquent_model(): void
    {
        $this->assertInstanceOf(Model::class, $this->model);
    }

    #[Test]
    public function it_uses_expected_traits(): void
    {
        $this->assertHasTraits([
            'Illuminate\Database\Eloquent\Factories\HasFactory',
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
    public function it_uses_timestamps(): void
    {
        $this->assertUsesTimestamps();
    }

    #[Test]
    public function it_has_correct_primary_key_configuration(): void
    {
        $this->assertEquals('uuid', $this->model->getKeyName());
        $this->assertFalse($this->model->getIncrementing());
        $this->assertEquals('string', $this->model->getKeyType());
    }

    #[Test]
    public function it_is_abstract_class(): void
    {
        $reflection = new \ReflectionClass(BaseUuidModel::class);
        $this->assertTrue($reflection->isAbstract());
    }
}