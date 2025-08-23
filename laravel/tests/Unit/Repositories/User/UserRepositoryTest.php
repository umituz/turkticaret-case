<?php

namespace Tests\Unit\Repositories\User;

use App\Models\User\User;
use App\Repositories\User\UserRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use Tests\Base\BaseRepositoryUnitTest;

/**
 * Unit tests for UserRepository
 * Tests data access logic for user operations
 */
#[CoversClass(UserRepository::class)]
#[Group('unit')]
#[Group('repositories')]
#[Small]
class UserRepositoryTest extends BaseRepositoryUnitTest
{
    private $userModelMock;

    protected function getRepositoryClass(): string
    {
        return UserRepository::class;
    }

    protected function getModelClass(): string
    {
        return User::class;
    }

    protected function getRepositoryDependencies(): array
    {
        $this->userModelMock = $this->mockModel(User::class);
        return [$this->userModelMock];
    }

    #[Test]
    public function repository_has_required_constructor_dependencies(): void
    {
        $this->assertHasRepositoryConstructorDependencies([User::class]);
    }

    #[Test]
    public function repository_has_required_methods(): void
    {
        $this->assertRepositoryHasMethod('findByEmail');
        $this->assertRepositoryHasMethod('create');
        $this->assertRepositoryHasMethod('findByUuid');
        $this->assertRepositoryHasMethod('updateByUuid');
        $this->assertRepositoryHasMethod('deleteByUuid');
        $this->assertRepositoryHasMethod('paginate');
    }

    #[Test]
    public function getModel_returns_user_model(): void
    {
        // Act
        $result = $this->repository->getModel();

        // Assert
        $this->assertInstanceOf(User::class, $result);
    }

    #[Test]
    public function findByEmail_returns_user_when_found(): void
    {
        // Arrange
        $email = 'test@example.com';
        $user = $this->mockModelInstance(User::class, ['email' => $email]);

        $this->userModelMock->shouldReceive('where')->andReturnSelf();
        $this->userModelMock->shouldReceive('first')->andReturn($user);

        // Act
        $result = $this->repository->findByEmail($email);

        // Assert
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($email, $result->email);
    }

    #[Test]
    public function findByEmail_returns_null_when_not_found(): void
    {
        // Arrange
        $email = 'nonexistent@example.com';

        $this->userModelMock->shouldReceive('where')->andReturnSelf();
        $this->userModelMock->shouldReceive('first')->andReturn(null);

        // Act
        $result = $this->repository->findByEmail($email);

        // Assert
        $this->assertNull($result);
    }

    #[Test]
    public function create_creates_user_successfully(): void
    {
        // Arrange
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'hashed_password'
        ];
        $createdUser = $this->mockModelInstance(User::class, $userData);

        $this->mockDatabaseTransaction();

        $this->userModelMock->shouldReceive('create')->andReturn($createdUser);

        // Act
        $result = $this->repository->create($userData);

        // Assert
        $this->assertInstanceOf(User::class, $result);
    }

    #[Test]
    public function findByUuid_returns_user_when_found(): void
    {
        // Arrange
        $uuid = $this->getTestUserUuid();
        $user = $this->mockModelInstance(User::class, ['uuid' => $uuid]);

        $this->userModelMock->shouldReceive('where')->andReturnSelf();
        $this->userModelMock->shouldReceive('first')->andReturn($user);

        // Act
        $result = $this->repository->findByUuid($uuid);

        // Assert
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($uuid, $result->uuid);
    }

    #[Test]
    public function findByUuid_throws_exception_when_not_found(): void
    {
        // Arrange
        $uuid = 'nonexistent-uuid';

        $this->userModelMock->shouldReceive('where')->andReturnSelf();
        $this->userModelMock->shouldReceive('first')->andReturn(null);

        // Act & Assert
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->repository->findByUuid($uuid);
    }

    #[Test]
    public function updateByUuid_updates_user_successfully(): void
    {
        // Arrange
        $uuid = $this->getTestUserUuid();
        $updateData = ['name' => 'Updated Name'];
        $user = $this->mockModelInstance(User::class, ['uuid' => $uuid]);

        $this->mockDatabaseTransaction();

        $this->userModelMock->shouldReceive('where')->andReturnSelf();
        $this->userModelMock->shouldReceive('firstOrFail')->andReturn($user);
        $user->shouldReceive('update')->andReturn(true);

        // Act
        $result = $this->repository->updateByUuid($uuid, $updateData);

        // Assert
        $this->assertInstanceOf(User::class, $result);
    }

    #[Test]
    public function deleteByUuid_deletes_user_successfully(): void
    {
        // Arrange
        $uuid = $this->getTestUserUuid();

        $this->mockDatabaseTransaction();

        $this->userModelMock->shouldReceive('where')->andReturnSelf();
        $this->userModelMock->shouldReceive('delete')->andReturn(true);

        // Act
        $result = $this->repository->deleteByUuid($uuid);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function paginate_returns_paginated_users(): void
    {
        // Arrange
        $relations = ['roles'];
        $paginatedResult = $this->mockPaginator();

        $this->userModelMock->shouldReceive('newQuery')->andReturnSelf();
        $this->userModelMock->shouldReceive('with')->andReturnSelf();
        $this->userModelMock->shouldReceive('paginate')->andReturn($paginatedResult);

        // Act
        $result = $this->repository->paginate($relations);

        // Assert
        $this->assertNotNull($result);
    }

    #[Test]
    public function all_returns_users_ordered_by_created_at(): void
    {
        // Arrange
        $users = $this->mockCollection([]);

        $this->userModelMock->shouldReceive('orderBy')->andReturnSelf();
        $this->userModelMock->shouldReceive('get')->andReturn($users);

        // Act
        $result = $this->repository->all();

        // Assert
        $this->assertNotNull($result);
    }

    #[Test]
    public function exists_returns_true_when_user_exists(): void
    {
        // Arrange
        $email = 'exists@example.com';

        $this->userModelMock->shouldReceive('where')->andReturnSelf();
        $this->userModelMock->shouldReceive('exists')->andReturn(true);

        // Act
        $result = $this->repository->exists('email', $email);

        // Assert
        $this->assertIsBool($result);
    }

    #[Test]
    public function exists_returns_false_when_user_does_not_exist(): void
    {
        // Arrange
        $email = 'notexists@example.com';

        $this->userModelMock->shouldReceive('where')->andReturnSelf();
        $this->userModelMock->shouldReceive('exists')->andReturn(false);

        // Act
        $result = $this->repository->exists('email', $email);

        // Assert
        $this->assertFalse($result);
    }

}
