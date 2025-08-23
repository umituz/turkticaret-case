<?php

namespace Tests\Unit\Http\Resources\Profile;

use App\Http\Resources\User\Profile\ProfileResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use Tests\Base\BaseResourceUnitTest;

/**
 * Unit tests for ProfileResource
 * Tests user profile response formatting with full user data
 */
#[CoversClass(ProfileResource::class)]
#[Group('unit')]
#[Group('resources')]
#[Small]
class ProfileResourceTest extends BaseResourceUnitTest
{
    protected function getResourceClass(): string
    {
        return ProfileResource::class;
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
        $resource = new ProfileResource($user);
        $result = $resource->toArray($request);

        // Assert
        $this->assertResourceArrayStructure([
            'uuid',
            'name',
            'email',
            'email_verified_at',
            'created_at',
            'updated_at',
        ], $result);

        $this->assertEquals($userData['uuid'], $result['uuid']);
        $this->assertEquals($userData['name'], $result['name']);
        $this->assertEquals($userData['email'], $result['email']);
    }

    #[Test]
    public function toArray_excludes_password(): void
    {
        // Arrange
        $userData = $this->getResourceData();
        $user = $this->createMockModel($userData);
        $request = new Request();

        // Act
        $resource = new ProfileResource($user);
        $result = $resource->toArray($request);

        // Assert
        $this->assertArrayNotHasKey('password', $result);
    }

    #[Test]
    public function toArray_includes_verification_timestamp(): void
    {
        // Arrange
        $verifiedAt = Carbon::parse('2024-01-10 09:15:00');
        $userData = array_merge($this->getResourceData(), [
            'email_verified_at' => $verifiedAt,
        ]);
        $user = $this->createMockModel($userData);
        $request = new Request();

        // Act
        $resource = new ProfileResource($user);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals($verifiedAt->toIso8601String(), $result['email_verified_at']);
    }

    #[Test]
    public function toArray_handles_null_email_verification(): void
    {
        // Arrange
        $userData = array_merge($this->getResourceData(), [
            'email_verified_at' => null,
        ]);
        $user = $this->createMockModel($userData);
        $request = new Request();

        // Act
        $resource = new ProfileResource($user);
        $result = $resource->toArray($request);

        // Assert
        $this->assertNull($result['email_verified_at']);
    }

    #[Test]
    public function toArray_formats_timestamps_as_iso8601(): void
    {
        // Arrange
        $createdAt = Carbon::parse('2024-01-01 12:00:00');
        $updatedAt = Carbon::parse('2024-01-15 15:30:00');
        $userData = array_merge($this->getResourceData(), [
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ]);
        $user = $this->createMockModel($userData);
        $request = new Request();

        // Act
        $resource = new ProfileResource($user);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals($createdAt->toIso8601String(), $result['created_at']);
        $this->assertEquals($updatedAt->toIso8601String(), $result['updated_at']);
    }

    #[Test]
    public function toArray_handles_null_timestamps(): void
    {
        // Arrange
        $userData = array_merge($this->getResourceData(), [
            'created_at' => null,
            'updated_at' => null,
        ]);
        $user = $this->createMockModel($userData);
        $request = new Request();

        // Act
        $resource = new ProfileResource($user);
        $result = $resource->toArray($request);

        // Assert
        $this->assertNull($result['created_at']);
        $this->assertNull($result['updated_at']);
    }

    #[Test]
    public function toArray_provides_complete_profile_data(): void
    {
        // Arrange
        $verifiedAt = Carbon::parse('2024-01-05 14:20:00');
        $createdAt = Carbon::parse('2024-01-01 10:00:00');
        $updatedAt = Carbon::parse('2024-01-15 16:45:00');

        $userData = [
            'uuid' => 'profile-user-uuid',
            'name' => 'Profile User',
            'email' => 'profile@example.com',
            'email_verified_at' => $verifiedAt,
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];
        $user = $this->createMockModel($userData);
        $request = new Request();

        // Act
        $resource = new ProfileResource($user);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('profile-user-uuid', $result['uuid']);
        $this->assertEquals('Profile User', $result['name']);
        $this->assertEquals('profile@example.com', $result['email']);
        $this->assertEquals($verifiedAt->toIso8601String(), $result['email_verified_at']);
        $this->assertEquals($createdAt->toIso8601String(), $result['created_at']);
        $this->assertEquals($updatedAt->toIso8601String(), $result['updated_at']);
    }

    #[Test]
    public function toArray_handles_unverified_profile(): void
    {
        // Arrange
        $userData = array_merge($this->getResourceData(), [
            'email_verified_at' => null,
        ]);
        $user = $this->createMockModel($userData);
        $request = new Request();

        // Act
        $resource = new ProfileResource($user);
        $result = $resource->toArray($request);

        // Assert
        $this->assertArrayHasKey('email_verified_at', $result);
        $this->assertNull($result['email_verified_at']);
        $this->assertNotNull($result['created_at']);
        $this->assertNotNull($result['updated_at']);
    }

    #[Test]
    public function toArray_preserves_user_identity_data(): void
    {
        // Arrange
        $testUuid = 'f47ac10b-58cc-4372-a567-0e02b2c3d479';
        $testEmail = 'user.identity@example.com';
        $userData = array_merge($this->getResourceData(), [
            'uuid' => $testUuid,
            'email' => $testEmail,
        ]);
        $user = $this->createMockModel($userData);
        $request = new Request();

        // Act
        $resource = new ProfileResource($user);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals($testUuid, $result['uuid']);
        $this->assertEquals($testEmail, $result['email']);
        $this->assertIsString($result['uuid']);
        $this->assertIsString($result['email']);
    }

    #[Test]
    public function toArray_supports_complex_name_formats(): void
    {
        // Arrange
        $userData = array_merge($this->getResourceData(), [
            'name' => 'Dr. María José González-Smith, PhD',
        ]);
        $user = $this->createMockModel($userData);
        $request = new Request();

        // Act
        $resource = new ProfileResource($user);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals('Dr. María José González-Smith, PhD', $result['name']);
    }

    #[Test]
    public function toArray_maintains_timestamp_precision(): void
    {
        // Arrange
        $preciseTime = Carbon::parse('2024-01-15 10:30:45.123456');
        $userData = array_merge($this->getResourceData(), [
            'created_at' => $preciseTime,
            'updated_at' => $preciseTime,
        ]);
        $user = $this->createMockModel($userData);
        $request = new Request();

        // Act
        $resource = new ProfileResource($user);
        $result = $resource->toArray($request);

        // Assert
        $this->assertEquals($preciseTime->toIso8601String(), $result['created_at']);
        $this->assertEquals($preciseTime->toIso8601String(), $result['updated_at']);
    }
}
