<?php

namespace Tests\Unit\Models\Auth;

use App\Models\Auth\User;
use App\Models\Cart\Cart;
use App\Models\Order\Order;
use Tests\Base\BaseModelUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Foundation\Auth\User as Authenticatable;

#[CoversClass(User::class)]
class UserTest extends BaseModelUnitTest
{
    protected function getModelClass(): string
    {
        return User::class;
    }

    #[Test]
    public function it_extends_authenticatable(): void
    {
        $this->assertInstanceOf(Authenticatable::class, $this->model);
    }

    #[Test]
    public function it_uses_expected_traits(): void
    {
        $this->assertHasTraits([
            'Laravel\Sanctum\HasApiTokens',
            'Illuminate\Database\Eloquent\Factories\HasFactory',
            'Spatie\Permission\Traits\HasRoles',
            'Illuminate\Database\Eloquent\Concerns\HasUuids',
            'Illuminate\Notifications\Notifiable',
        ]);
    }

    #[Test]
    public function it_has_uuid_primary_key(): void
    {
        $this->assertUsesUuidPrimaryKey();
    }

    #[Test]
    public function it_has_correct_fillable_attributes(): void
    {
        $expectedFillable = [
            'name',
            'email',
            'password',
            'language_uuid',
            'country_uuid',
        ];

        $this->assertHasFillable($expectedFillable);
    }

    #[Test]
    public function it_has_correct_hidden_attributes(): void
    {
        $expectedHidden = [
            'password',
            'remember_token',
        ];

        $this->assertHasHidden($expectedHidden);
    }

    #[Test]
    public function it_has_correct_casts(): void
    {
        $expectedCasts = [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];

        $this->assertHasCasts($expectedCasts);
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
    public function it_has_orders_relationship_method(): void
    {
        $this->assertHasRelationshipMethod('orders');
    }

    #[Test]
    public function it_has_factory_method(): void
    {
        $this->assertHasFactory();
    }

    #[Test]
    public function it_sets_password_attribute_with_hashing(): void
    {
        $originalPassword = 'plain-password';

        // Mock Hash facade for Laravel's hashed cast
        $this->app['hash']->shouldReceive('isHashed')
            ->andReturn(false);

        $this->app['hash']->shouldReceive('make')
            ->andReturnUsing(function ($password) {
                return password_hash($password, PASSWORD_DEFAULT);
            });

        // Laravel 11+ uses 'hashed' cast for automatic password hashing
        $this->model->password = $originalPassword;

        // The password should be hashed (not equal to original)
        $actualPassword = $this->model->getAttributes()['password'];
        $this->assertNotEquals($originalPassword, $actualPassword);

        // Verify the password was properly hashed
        $this->assertIsString($actualPassword);
        $this->assertNotEmpty($actualPassword);

        // Verify it's a proper bcrypt hash
        $this->assertMatchesRegularExpression('/^\$2y\$/', $actualPassword);

        // Verify the hash works with the original password
        $this->assertTrue(password_verify($originalPassword, $actualPassword));
    }

    #[Test]
    public function it_can_access_cart_relationship(): void
    {
        $cartRelation = $this->model->cart();

        $this->assertEquals('App\Models\Cart\Cart', $cartRelation->getRelated()::class);
        $this->assertEquals('user_uuid', $cartRelation->getForeignKeyName());
        $this->assertEquals('uuid', $cartRelation->getLocalKeyName());
    }

    #[Test]
    public function it_can_access_orders_relationship(): void
    {
        $ordersRelation = $this->model->orders();

        $this->assertEquals('App\Models\Order\Order', $ordersRelation->getRelated()::class);
        $this->assertEquals('user_uuid', $ordersRelation->getForeignKeyName());
        $this->assertEquals('uuid', $ordersRelation->getLocalKeyName());
    }
}
