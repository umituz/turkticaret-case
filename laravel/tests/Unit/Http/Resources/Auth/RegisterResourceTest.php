<?php

namespace Tests\Unit\Http\Resources\Auth;

use App\Http\Resources\Auth\RegisterResource;
use Tests\Base\BaseResourceUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Unit tests for RegisterResource
 * Tests user registration response formatting
 */
#[CoversClass(RegisterResource::class)]
#[Group('unit')]
#[Group('resources')]
#[Small]
class RegisterResourceTest extends BaseResourceUnitTest
{
    protected function getResourceClass(): string
    {
        return RegisterResource::class;
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
        $resource = new RegisterResource($user);
        $result = $resource->toArray($request);

        // Assert
        $this->assertResourceArrayStructure([
            'uuid',
            'name',
            'email',
            'created_at',
        ], $result);

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
        $resource = new RegisterResource($user);
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
        $resource = new RegisterResource($user);
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
        $resource = new RegisterResource($user);
        $result = $resource->toArray($request);

        // Assert
        $this->assertNull($result['created_at']);
    }

    #[Test]
    public function toArray_includes_all_user_identification_fields(): void
    {
        // Arrange
        $userData = [
            'uuid' => 'test-user-uuid',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'created_at' => Carbon::now(),
        ];
        $user = $this->createMockModel($userData);
        $request = new Request();

        // Act
        $resource = new RegisterResource($user);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('test-user-uuid', $result['uuid']);
        $this->assertEquals('Test User', $result['name']);
        $this->assertEquals('test@example.com', $result['email']);
        $this->assertNotNull($result['created_at']);
    }

    #[Test]
    public function toArray_handles_empty_name(): void
    {
        // Arrange
        $userData = array_merge($this->getResourceData(), [
            'name' => '',
        ]);
        $user = $this->createMockModel($userData);
        $request = new Request();

        // Act
        $resource = new RegisterResource($user);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('', $result['name']);
    }

    #[Test]
    public function toArray_preserves_uuid_format(): void
    {
        // Arrange
        $testUuid = 'f47ac10b-58cc-4372-a567-0e02b2c3d479';
        $userData = array_merge($this->getResourceData(), [
            'uuid' => $testUuid,
        ]);
        $user = $this->createMockModel($userData);
        $request = new Request();

        // Act
        $resource = new RegisterResource($user);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals($testUuid, $result['uuid']);
        $this->assertIsString($result['uuid']);
    }

    #[Test]
    public function toArray_validates_email_format_preservation(): void
    {
        // Arrange
        $testEmail = 'user.test+123@example.com';
        $userData = array_merge($this->getResourceData(), [
            'email' => $testEmail,
        ]);
        $user = $this->createMockModel($userData);
        $request = new Request();

        // Act
        $resource = new RegisterResource($user);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals($testEmail, $result['email']);
        $this->assertIsString($result['email']);
    }
}