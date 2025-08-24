<?php

namespace Tests\Unit\Services\Auth;

use App\Services\Auth\RegisterService;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\Country\CountryService;
use App\Services\Language\LanguageService;
use App\DTOs\Auth\RegisterDTO;
use App\Jobs\User\SendWelcomeEmailJob;
use App\Models\User\User;
use App\Models\Country\Country;
use App\Models\Language\Language;
use Tests\Base\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\PersonalAccessToken;
use Mockery;

/**
 * Unit tests for RegisterService
 * Tests user registration with repository mocking
 */
#[CoversClass(RegisterService::class)]
#[Group('unit')]
#[Group('services')]
#[Small]
class RegisterServiceTest extends UnitTestCase
{
    private RegisterService $service;
    private UserRepositoryInterface $mockUserRepository;
    private CountryService $mockCountryService;
    private LanguageService $mockLanguageService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockUserRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->mockCountryService = Mockery::mock(CountryService::class);
        $this->mockLanguageService = Mockery::mock(LanguageService::class);
        
        $this->service = new RegisterService(
            $this->mockUserRepository,
            $this->mockCountryService,
            $this->mockLanguageService
        );
    }

    #[Test]
    public function register_successfully_creates_user_with_token(): void
    {
        // Arrange
        Queue::fake();
        
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'countryCode' => 'US'
        ];

        $country = Mockery::mock(Country::class);
        $country->uuid = 'country-uuid';
        $country->locale = 'en-US';

        $language = Mockery::mock(Language::class);
        $language->uuid = 'language-uuid';

        $user = Mockery::mock(User::class);
        $user->shouldReceive('createToken')
            ->once()
            ->with('auth-token')
            ->andReturn(Mockery::mock(['plainTextToken' => 'test-token']));

        RegisterDTO::shouldReceive('fromArray')
            ->once()
            ->with($data)
            ->andReturn(Mockery::mock(RegisterDTO::class, [
                'name' => 'John Doe',
                'email' => 'john@example.com', 
                'password' => 'password123',
                'countryCode' => 'US'
            ]));

        $this->mockCountryService->shouldReceive('findByCode')
            ->once()
            ->with('US')
            ->andReturn($country);

        $this->mockLanguageService->shouldReceive('getByCountryLocale')
            ->once()
            ->with('en-US')
            ->andReturn($language);

        $this->mockUserRepository->shouldReceive('create')
            ->once()
            ->with([
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 'password123',
                'country_uuid' => 'country-uuid',
                'language_uuid' => 'language-uuid',
            ])
            ->andReturn($user);

        // Act
        $result = $this->service->register($data);

        // Assert
        $this->assertEquals($user, $result['user']);
        $this->assertEquals('test-token', $result['token']);
        Queue::assertPushed(SendWelcomeEmailJob::class);
    }

    #[Test]
    public function register_dispatches_welcome_email_job(): void
    {
        // Arrange
        Queue::fake();
        
        $data = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'countryCode' => 'TR'
        ];

        $country = Mockery::mock(Country::class);
        $country->uuid = 'country-uuid';
        $country->locale = 'tr-TR';

        $language = Mockery::mock(Language::class);
        $language->uuid = 'language-uuid';

        $user = Mockery::mock(User::class);
        $user->shouldReceive('createToken')
            ->once()
            ->with('auth-token')
            ->andReturn(Mockery::mock(['plainTextToken' => 'test-token']));

        RegisterDTO::shouldReceive('fromArray')
            ->once()
            ->with($data)
            ->andReturn(Mockery::mock(RegisterDTO::class, [
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'password' => 'password123',
                'countryCode' => 'TR'
            ]));

        $this->mockCountryService->shouldReceive('findByCode')
            ->once()
            ->with('TR')
            ->andReturn($country);

        $this->mockLanguageService->shouldReceive('getByCountryLocale')
            ->once()
            ->with('tr-TR')
            ->andReturn($language);

        $this->mockUserRepository->shouldReceive('create')
            ->once()
            ->andReturn($user);

        // Act
        $this->service->register($data);

        // Assert
        Queue::assertPushed(SendWelcomeEmailJob::class, function ($job) use ($user) {
            return $job->user === $user;
        });
    }

    #[Test]
    public function register_creates_auth_token_for_user(): void
    {
        // Arrange
        Queue::fake();
        
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'countryCode' => 'GB'
        ];

        $country = Mockery::mock(Country::class);
        $country->uuid = 'country-uuid';
        $country->locale = 'en-GB';

        $language = Mockery::mock(Language::class);
        $language->uuid = 'language-uuid';

        $user = Mockery::mock(User::class);
        $tokenResult = Mockery::mock();
        $tokenResult->plainTextToken = 'generated-auth-token';
        
        $user->shouldReceive('createToken')
            ->once()
            ->with('auth-token')
            ->andReturn($tokenResult);

        RegisterDTO::shouldReceive('fromArray')
            ->once()
            ->with($data)
            ->andReturn(Mockery::mock(RegisterDTO::class, [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123',
                'countryCode' => 'GB'
            ]));

        $this->mockCountryService->shouldReceive('findByCode')
            ->once()
            ->with('GB')
            ->andReturn($country);

        $this->mockLanguageService->shouldReceive('getByCountryLocale')
            ->once()
            ->with('en-GB')
            ->andReturn($language);

        $this->mockUserRepository->shouldReceive('create')
            ->once()
            ->andReturn($user);

        // Act
        $result = $this->service->register($data);

        // Assert
        $this->assertEquals('generated-auth-token', $result['token']);
    }

    #[Test]
    public function register_uses_country_and_language_services(): void
    {
        // Arrange
        Queue::fake();
        
        $data = [
            'name' => 'International User',
            'email' => 'intl@example.com',
            'password' => 'password123',
            'countryCode' => 'DE'
        ];

        $country = Mockery::mock(Country::class);
        $country->uuid = 'de-country-uuid';
        $country->locale = 'de-DE';

        $language = Mockery::mock(Language::class);
        $language->uuid = 'german-language-uuid';

        $user = Mockery::mock(User::class);
        $user->shouldReceive('createToken')
            ->once()
            ->andReturn(Mockery::mock(['plainTextToken' => 'test-token']));

        RegisterDTO::shouldReceive('fromArray')
            ->once()
            ->with($data)
            ->andReturn(Mockery::mock(RegisterDTO::class, [
                'name' => 'International User',
                'email' => 'intl@example.com',
                'password' => 'password123',
                'countryCode' => 'DE'
            ]));

        $this->mockCountryService->shouldReceive('findByCode')
            ->once()
            ->with('DE')
            ->andReturn($country);

        $this->mockLanguageService->shouldReceive('getByCountryLocale')
            ->once()
            ->with('de-DE')
            ->andReturn($language);

        $this->mockUserRepository->shouldReceive('create')
            ->once()
            ->with([
                'name' => 'International User',
                'email' => 'intl@example.com',
                'password' => 'password123',
                'country_uuid' => 'de-country-uuid',
                'language_uuid' => 'german-language-uuid',
            ])
            ->andReturn($user);

        // Act
        $result = $this->service->register($data);

        // Assert
        $this->assertSame($user, $result['user']);
    }

    #[Test]
    public function register_returns_both_user_and_token(): void
    {
        // Arrange
        Queue::fake();
        
        $data = [
            'name' => 'Complete User',
            'email' => 'complete@example.com',
            'password' => 'password123',
            'countryCode' => 'FR'
        ];

        $country = Mockery::mock(Country::class);
        $country->uuid = 'fr-uuid';
        $country->locale = 'fr-FR';

        $language = Mockery::mock(Language::class);
        $language->uuid = 'fr-lang-uuid';

        $user = Mockery::mock(User::class);
        $user->shouldReceive('createToken')
            ->once()
            ->andReturn(Mockery::mock(['plainTextToken' => 'complete-token']));

        RegisterDTO::shouldReceive('fromArray')
            ->once()
            ->andReturn(Mockery::mock(RegisterDTO::class, [
                'name' => 'Complete User',
                'email' => 'complete@example.com',
                'password' => 'password123',
                'countryCode' => 'FR'
            ]));

        $this->mockCountryService->shouldReceive('findByCode')
            ->once()
            ->andReturn($country);

        $this->mockLanguageService->shouldReceive('getByCountryLocale')
            ->once()
            ->andReturn($language);

        $this->mockUserRepository->shouldReceive('create')
            ->once()
            ->andReturn($user);

        // Act
        $result = $this->service->register($data);

        // Assert
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertSame($user, $result['user']);
        $this->assertEquals('complete-token', $result['token']);
    }
}