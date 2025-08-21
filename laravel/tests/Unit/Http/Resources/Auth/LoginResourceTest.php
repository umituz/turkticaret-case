<?php

namespace Tests\Unit\Http\Resources\Auth;

use App\Http\Resources\Auth\LoginResource;
use Tests\Base\BaseResourceUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Unit tests for LoginResource
 * Tests user login response formatting
 */
#[CoversClass(LoginResource::class)]
#[Group('unit')]
#[Group('resources')]
#[Small]
class LoginResourceTest extends BaseResourceUnitTest
{
    protected function getResourceClass(): string
    {
        return LoginResource::class;
    }

    protected function getResourceData(): array
    {
        return [
            'uuid' => $this->generateTestUuid(),
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'hashed-password',
            'email_verified_at' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    #[Test]
    public function resource_extends_json_resource(): void
    {
        $this->assertResourceExtendsJsonResource();
    }

    #[Test]
    public function resource_has_required_methods(): void
    {
        $this->assertResourceHasMethod('toArray');
    }

    #[Test]
    public function toArray_returns_correct_structure(): void
    {
        // Arrange
        $userData = $this->getResourceData();
        $user = $this->createMockModel($userData);
        $request = new Request();

        // Act
        $resource = new LoginResource($user);
        $result = $resource->toArray($request);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('uuid', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('email', $result);
        $this->assertArrayHasKey('created_at', $result);

        $this->assertEquals($userData['uuid'], $result['uuid']);
        $this->assertEquals($userData['name'], $result['name']);
        $this->assertEquals($userData['email'], $result['email']);
    }

    #[Test]
    public function toArray_excludes_sensitive_data(): void
    {
        // Arrange
        $userData = $this->getResourceData();
        $user = $this->createMockModel($userData);
        $request = new Request();

        // Act
        $resource = new LoginResource($user);
        $result = $resource->toArray($request);

        // Assert
        $this->assertArrayNotHasKey('password', $result);
        $this->assertArrayNotHasKey('email_verified_at', $result);
        $this->assertArrayNotHasKey('updated_at', $result);
    }

    #[Test]
    public function toArray_formats_created_at_as_iso8601(): void
    {
        // Arrange
        $createdAt = Carbon::parse('2024-01-15 10:30:00');
        $userData = array_merge($this->getResourceData(), [
            'created_at' => $createdAt,
        ]);
        $user = $this->createMockModel($userData);
        $request = new Request();

        // Act
        $resource = new LoginResource($user);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals($createdAt->toIso8601String(), $result['created_at']);
    }

    #[Test]
    public function toArray_handles_null_created_at(): void
    {
        // Arrange
        $userData = array_merge($this->getResourceData(), [
            'created_at' => null,
        ]);
        $user = $this->createMockModel($userData);
        $request = new Request();

        // Act
        $resource = new LoginResource($user);
        $result = $resource->toArray($request);

        // Assert
        $this->assertNull($result['created_at']);
    }

    #[Test]
    public function toArray_provides_authenticated_user_data(): void
    {
        // Arrange
        $userData = [
            'uuid' => 'auth-user-uuid',
            'name' => 'Authenticated User',
            'email' => 'auth@example.com',
            'created_at' => Carbon::now(),
        ];
        $user = $this->createMockModel($userData);
        $request = new Request();

        // Act
        $resource = new LoginResource($user);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('auth-user-uuid', $result['uuid']);
        $this->assertEquals('Authenticated User', $result['name']);
        $this->assertEquals('auth@example.com', $result['email']);
        $this->assertNotNull($result['created_at']);
    }

    #[Test]
    public function toArray_handles_special_characters_in_name(): void
    {
        // Arrange
        $userData = array_merge($this->getResourceData(), [
            'name' => 'John O\'Connor & María José',
        ]);
        $user = $this->createMockModel($userData);
        $request = new Request();

        // Act
        $resource = new LoginResource($user);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('John O\'Connor & María José', $result['name']);
    }

    #[Test]
    public function toArray_preserves_uuid_integrity(): void
    {
        // Arrange
        $testUuid = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
        $userData = array_merge($this->getResourceData(), [
            'uuid' => $testUuid,
        ]);
        $user = $this->createMockModel($userData);
        $request = new Request();

        // Act
        $resource = new LoginResource($user);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals($testUuid, $result['uuid']);
        $this->assertIsString($result['uuid']);
    }

    #[Test]
    public function toArray_maintains_email_case_sensitivity(): void
    {
        // Arrange
        $testEmail = 'User.Test@Example.COM';
        $userData = array_merge($this->getResourceData(), [
            'email' => $testEmail,
        ]);
        $user = $this->createMockModel($userData);
        $request = new Request();

        // Act
        $resource = new LoginResource($user);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals($testEmail, $result['email']);
    }

    #[Test]
    public function toArray_supports_unicode_characters(): void
    {
        // Arrange
        $userData = array_merge($this->getResourceData(), [
            'name' => 'Müller Özkan Θεός',
            'email' => 'müller@example.com',
        ]);
        $user = $this->createMockModel($userData);
        $request = new Request();

        // Act
        $resource = new LoginResource($user);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('Müller Özkan Θεός', $result['name']);
        $this->assertEquals('müller@example.com', $result['email']);
    }
}