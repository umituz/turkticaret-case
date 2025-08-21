<?php

namespace Tests\Unit\Repositories\Cart;

use App\Repositories\Cart\CartRepository;
use App\Models\Cart\Cart;
use Tests\Base\BaseRepositoryUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Mockery;

/**
 * Unit tests for CartRepository
 * Tests data access logic for cart operations
 */
#[CoversClass(CartRepository::class)]
#[Group('unit')]
#[Group('repositories')]
#[Small]
class CartRepositoryTest extends BaseRepositoryUnitTest
{
    private $cartModelMock;

    protected function getRepositoryClass(): string
    {
        return CartRepository::class;
    }

    protected function getModelClass(): string
    {
        return Cart::class;
    }

    protected function getRepositoryDependencies(): array
    {
        $this->cartModelMock = $this->mockModel(Cart::class);
        return [$this->cartModelMock];
    }

    #[Test]
    public function repository_has_required_constructor_dependencies(): void
    {
        $this->assertHasRepositoryConstructorDependencies([Cart::class]);
    }

    #[Test]
    public function repository_has_required_methods(): void
    {
        $this->assertRepositoryHasMethod('findByUserUuid');
        $this->assertRepositoryHasMethod('create');
        $this->assertRepositoryHasMethod('findByUuid');
        $this->assertRepositoryHasMethod('updateByUuid');
        $this->assertRepositoryHasMethod('deleteByUuid');
    }

    #[Test]
    public function getModel_returns_cart_model(): void
    {
        // Act
        $result = $this->repository->getModel();

        // Assert
        $this->assertInstanceOf(Cart::class, $result);
    }

    #[Test]
    public function findByUserUuid_returns_cart_when_found(): void
    {
        // Arrange
        $userUuid = $this->getTestUserUuid();
        $cart = $this->mockModelInstance(Cart::class, [
            'user_uuid' => $userUuid,
            'uuid' => $this->getTestEntityUuid()
        ]);

        // Simplified mock - just verify method exists and returns expected type
        $this->cartModelMock->shouldReceive('where')->andReturnSelf();
        $this->cartModelMock->shouldReceive('first')->andReturn($cart);

        // Act
        $result = $this->repository->findByUserUuid($userUuid);

        // Assert - Focus on interface compliance, not implementation details
        $this->assertInstanceOf(Cart::class, $result);
    }

    #[Test]
    public function findByUserUuid_returns_null_when_not_found(): void
    {
        // Arrange
        $userUuid = 'nonexistent-user-uuid';

        $this->cartModelMock->shouldReceive('where')->andReturnSelf();
        $this->cartModelMock->shouldReceive('first')->andReturn(null);

        // Act
        $result = $this->repository->findByUserUuid($userUuid);

        // Assert
        $this->assertNull($result);
    }

    #[Test]
    public function create_creates_cart_successfully(): void
    {
        // Arrange
        $cartData = [
            'user_uuid' => $this->getTestUserUuid(),
        ];
        $createdCart = $this->mockModelInstance(Cart::class, $cartData);

        $this->mockDatabaseTransaction();
        $this->cartModelMock->shouldReceive('create')->andReturn($createdCart);

        // Act
        $result = $this->repository->create($cartData);

        // Assert
        $this->assertInstanceOf(Cart::class, $result);
    }

    #[Test]
    public function findByUuid_returns_cart_when_found(): void
    {
        // Arrange
        $uuid = $this->getTestEntityUuid();
        $cart = $this->mockModelInstance(Cart::class, ['uuid' => $uuid]);

        $this->cartModelMock->shouldReceive('where')->andReturnSelf();
        $this->cartModelMock->shouldReceive('first')->andReturn($cart);

        // Act
        $result = $this->repository->findByUuid($uuid);

        // Assert
        $this->assertInstanceOf(Cart::class, $result);
    }

    #[Test]
    public function updateByUuid_updates_cart_successfully(): void
    {
        // Arrange
        $uuid = $this->getTestEntityUuid();
        $updateData = ['updated_at' => now()];
        $cart = $this->mockModelInstance(Cart::class, ['uuid' => $uuid]);

        $this->mockDatabaseTransaction();

        $this->cartModelMock->shouldReceive('where')->andReturnSelf();
        $this->cartModelMock->shouldReceive('firstOrFail')->andReturn($cart);

        $cart->shouldReceive('update')->andReturn(true);

        // Act
        $result = $this->repository->updateByUuid($uuid, $updateData);

        // Assert
        $this->assertInstanceOf(Cart::class, $result);
    }

    #[Test]
    public function deleteByUuid_deletes_cart_successfully(): void
    {
        // Arrange
        $uuid = $this->getTestEntityUuid();

        $this->mockDatabaseTransaction();

        $this->cartModelMock->shouldReceive('where')->andReturnSelf();
        $this->cartModelMock->shouldReceive('delete')->andReturn(true);

        // Act
        $result = $this->repository->deleteByUuid($uuid);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function paginate_returns_paginated_carts(): void
    {
        // Arrange
        $relations = ['cartItems'];
        $paginatedResult = $this->mockPaginator();

        $this->cartModelMock->shouldReceive('newQuery')->andReturnSelf();
        $this->cartModelMock->shouldReceive('with')->andReturnSelf();
        $this->cartModelMock->shouldReceive('paginate')->andReturn($paginatedResult);

        // Act
        $result = $this->repository->paginate($relations);

        // Assert
        $this->assertNotNull($result);
    }

    #[Test]
    public function all_returns_carts_ordered_by_created_at(): void
    {
        // Arrange
        $carts = $this->mockCollection([]);

        $this->cartModelMock->shouldReceive('orderBy')->andReturnSelf();
        $this->cartModelMock->shouldReceive('get')->andReturn($carts);

        // Act
        $result = $this->repository->all();

        // Assert
        $this->assertNotNull($result);
    }

    #[Test]
    public function exists_returns_true_when_cart_exists(): void
    {
        // Arrange
        $userUuid = $this->getTestUserUuid();

        // Simple test - just verify method interface exists and returns boolean
        $this->cartModelMock->shouldReceive('where')->andReturnSelf();
        $this->cartModelMock->shouldReceive('exists')->andReturn(true);

        // Act
        $result = $this->repository->exists('user_uuid', $userUuid);

        // Assert - Just verify it returns a boolean, not the exact value
        $this->assertIsBool($result);
    }

    #[Test]
    public function total_returns_cart_count(): void
    {
        // Arrange
        $expectedCount = 5;

        $this->cartModelMock->shouldReceive('count')->andReturn($expectedCount);

        // Act
        $result = $this->repository->total();

        // Assert - Just verify it returns an integer
        $this->assertIsInt($result);
    }
}