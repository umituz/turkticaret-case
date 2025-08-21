<?php

namespace Tests\Base;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\Container as ContainerContract;
use Illuminate\Contracts\Translation\Translator as TranslatorContract;
use Illuminate\Support\Facades\Facade;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory as ValidationFactory;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;

/**
 * Base test case for unit tests with PHPUnit 11+ compatibility
 * Provides Laravel container setup and common testing utilities for isolated unit testing
 */
#[Group('unit')]
#[Small]
abstract class UnitTestCase extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected ContainerContract $app;
    protected Translator $translator;
    protected ArrayLoader $translationLoader;
    protected ValidationFactory $validationFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupUnitTestEnvironment();
    }

    /**
     * Setup unit test environment with minimal Laravel dependencies
     */
    protected function setupUnitTestEnvironment(): void
    {
        // Create container with abort method
        $this->app = new class extends Container {
            public function abort($code, $message = '', array $headers = [])
            {
                throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException($message);
            }
        };
        Container::setInstance($this->app);

        // Setup translation
        $this->setupTranslation();

        // Setup validation
        $this->setupValidation();
        
        // Setup presence verifier for validation rules
        $this->setupPresenceVerifier();

        // Setup configuration
        $this->setupConfiguration();

        // Setup request mock
        $this->setupRequestMock();

        // Setup auth mock
        $this->setupAuthMock();

        // Setup queue mock
        $this->setupQueueMock();

        // Setup database mock
        $this->setupDatabaseMock();

        // Setup Hash facade mock
        $this->setupHashMock();

        // Setup Spatie Permission
        $this->setupSpatiePermission();

        // Set facade application
        Facade::setFacadeApplication($this->app);
    }

    /**
     * Setup translation services
     */
    protected function setupTranslation(): void
    {
        $this->translationLoader = new ArrayLoader();
        $this->translator = new Translator($this->translationLoader, 'en');

        $this->app->instance('translator', $this->translator);
        $this->app->instance(TranslatorContract::class, $this->translator);
    }

    /**
     * Setup validation services
     */
    protected function setupValidation(): void
    {
        $this->validationFactory = new ValidationFactory($this->translator);
        
        $this->app->instance('validator', $this->validationFactory);
        $this->app->instance(ValidationFactory::class, $this->validationFactory);
    }

    /**
     * Setup presence verifier for validation rules like exists/unique
     */
    protected function setupPresenceVerifier(): void
    {
        $presenceVerifier = Mockery::mock(\Illuminate\Validation\DatabasePresenceVerifierInterface::class);
        
        // For unit tests, mock both exists and unique to pass
        // exists rule: return 1 (record exists)
        // unique rule: return 0 (no existing records, so unique)
        $presenceVerifier->shouldReceive('getCount')
            ->andReturnUsing(function ($collection, $column, $value, $excludeId = null, $idColumn = null, $extra = []) {
                // For unique rules (when excludeId is provided or we want to check uniqueness)
                // return 0 to indicate no existing records
                return 0;
            });
            
        $presenceVerifier->shouldReceive('getMultiCount')
            ->andReturn(0);
            
        $presenceVerifier->shouldReceive('setConnection')
            ->andReturnSelf();
            
        $this->validationFactory->setPresenceVerifier($presenceVerifier);
    }

    /**
     * Setup configuration mock
     */
    protected function setupConfiguration(): void
    {
        $config = Mockery::mock();
        $config->shouldReceive('get')->withArgs(['app.locale'])->andReturn('en');
        $config->shouldReceive('get')->withArgs(['app.faker_locale', null])->andReturn('en_US');
        $config->shouldReceive('get')->withArgs(['auth.defaults.guard'])->andReturn('web');
        $config->shouldReceive('get')->withArgs(['auth.guards'])->andReturn([
            'web' => ['driver' => 'session', 'provider' => 'users'],
            'api' => ['driver' => 'token', 'provider' => 'users']
        ]);
        $config->shouldReceive('get')->withArgs(['permission.models.role'])->andReturn('App\Models\Authority\Role');
        $config->shouldReceive('get')->withArgs(['permission.models.permission'])->andReturn('App\Models\Authority\Permission');
        $config->shouldReceive('get')->withArgs(['permission.table_names.roles'])->andReturn('roles');
        $config->shouldReceive('get')->withArgs(['permission.table_names.permissions'])->andReturn('permissions');
        $config->shouldReceive('get')->withArgs(['permission.table_names.model_has_permissions'])->andReturn('model_has_permissions');
        $config->shouldReceive('get')->withArgs(['permission.table_names.model_has_roles'])->andReturn('model_has_roles');
        $config->shouldReceive('get')->withArgs(['permission.table_names.role_has_permissions'])->andReturn('role_has_permissions');
        $config->shouldReceive('get')->withArgs(['permission.column_names.role_pivot_key'])->andReturn('role_id');
        $config->shouldReceive('get')->withArgs(['permission.column_names.permission_pivot_key'])->andReturn('permission_id');
        $config->shouldReceive('get')->withArgs(['permission.column_names.model_morph_key'])->andReturn('model_id');
        $config->shouldReceive('get')->withArgs(['permission.column_names.team_foreign_key'])->andReturn('team_id');
        $config->shouldReceive('get')->withArgs(['permission.teams'])->andReturn(false);
        $config->shouldReceive('get')->withArgs(['permission.use_passport_client_credentials'])->andReturn(false);
        $config->shouldReceive('get')->withArgs(['permission.display_permission_in_exception'])->andReturn(false);
        $config->shouldReceive('get')->withArgs(['permission.display_role_in_exception'])->andReturn(false);
        $config->shouldReceive('get')->withArgs(['permission.enable_wildcard_permission'])->andReturn(false);
        $config->shouldReceive('get')->withArgs(['permission.cache.expiration_time'])->andReturn(0);
        $config->shouldReceive('get')->withArgs(['permission.cache.key'])->andReturn('spatie.permission.cache');
        $config->shouldReceive('get')->withArgs(['permission.cache.store'])->andReturn('default');
        $config->shouldReceive('get')->andReturnUsing(function ($key, $default = null) {
            return $default;
        });

        $this->app->instance('config', $config);
    }

    /**
     * Setup request mock
     */
    protected function setupRequestMock(): void
    {
        $request = Mockery::mock(\Illuminate\Http\Request::class);
        $request->shouldReceive('method')->andReturn('GET');
        $request->shouldReceive('path')->andReturn('');
        $request->shouldReceive('is')->andReturn(false);
        $request->shouldReceive('user')->andReturn(null);
        $request->shouldReceive('header')->andReturn(null);
        $request->shouldReceive('expectsJson')->andReturn(true);
        $request->shouldReceive('ip')->andReturn('127.0.0.1');
        $request->shouldReceive('userAgent')->andReturn('TestAgent/1.0');

        $this->app->instance('request', $request);
        $this->app->instance(\Illuminate\Http\Request::class, $request);
        
        // Register request() helper
        if (!function_exists('request')) {
            $this->app->singleton('request', function () use ($request) {
                return $request;
            });
        }
    }

    /**
     * Setup auth mock
     */
    protected function setupAuthMock(): void
    {
        $authUser = Mockery::mock();
        $authUser->uuid = 'test-user-uuid';
        $authUser->shouldReceive('getAttribute')->with('uuid')->andReturn('test-user-uuid');

        $auth = Mockery::mock();
        $auth->shouldReceive('user')->andReturn($authUser);
        $auth->shouldReceive('check')->andReturn(true);
        $auth->shouldReceive('guest')->andReturn(false);
        $auth->shouldReceive('id')->andReturn('test-user-uuid');

        $this->app->instance('auth', $auth);
        
        // Bind Auth Factory to support auth() helper
        $authFactory = Mockery::mock(\Illuminate\Contracts\Auth\Factory::class);
        $authFactory->shouldReceive('guard')->andReturn($auth);
        $authFactory->shouldReceive('user')->andReturn($authUser);
        $authFactory->shouldReceive('check')->andReturn(true);
        $authFactory->shouldReceive('guest')->andReturn(false);
        $authFactory->shouldReceive('id')->andReturn('test-user-uuid');
        $this->app->instance(\Illuminate\Contracts\Auth\Factory::class, $authFactory);
    }

    /**
     * Setup queue mock
     */
    protected function setupQueueMock(): void
    {
        $queue = Mockery::mock();
        $queue->shouldReceive('push')->andReturn(true);
        $queue->shouldReceive('pushOn')->andReturn(true);
        $queue->shouldReceive('later')->andReturn(true);
        $queue->shouldReceive('bulk')->andReturn(true);
        $queue->shouldReceive('size')->andReturn(0);
        $queue->shouldReceive('connection')->andReturnSelf();

        $this->app->instance('queue', $queue);
    }

    /**
     * Setup database mock
     */
    protected function setupDatabaseMock(): void
    {
        $queryBuilder = Mockery::mock(\Illuminate\Database\Query\Builder::class);
        $queryBuilder->shouldReceive('from')->andReturnSelf();
        $queryBuilder->shouldReceive('where')->andReturnSelf();
        $queryBuilder->shouldReceive('whereNotNull')->andReturnSelf();
        $queryBuilder->shouldReceive('select')->andReturnSelf();
        $queryBuilder->shouldReceive('first')->andReturn(null);
        $queryBuilder->shouldReceive('get')->andReturn(collect([]));
        $queryBuilder->shouldReceive('find')->andReturn(null);
        $queryBuilder->shouldReceive('findOrFail')->andThrow(new \Illuminate\Database\Eloquent\ModelNotFoundException());
        $queryBuilder->shouldReceive('firstOrFail')->andThrow(new \Illuminate\Database\Eloquent\ModelNotFoundException());

        $connection = Mockery::mock();
        $connection->shouldReceive('table')->andReturnSelf();
        $connection->shouldReceive('query')->andReturn($queryBuilder);
        $connection->shouldReceive('select')->andReturn([]);
        $connection->shouldReceive('insert')->andReturn(true);
        $connection->shouldReceive('update')->andReturn(1);
        $connection->shouldReceive('delete')->andReturn(1);
        $connection->shouldReceive('transaction')->andReturnUsing(function ($callback) {
            return $callback();
        });
        $connection->shouldReceive('beginTransaction')->andReturn(true);
        $connection->shouldReceive('commit')->andReturn(true);
        $connection->shouldReceive('rollBack')->andReturn(true);

        $resolver = Mockery::mock(\Illuminate\Database\ConnectionResolverInterface::class);
        $resolver->shouldReceive('connection')->andReturn($connection);

        // Set the resolver on Eloquent Model
        \Illuminate\Database\Eloquent\Model::setConnectionResolver($resolver);

        $this->app->instance('db', $connection);
        $this->app->instance('db.connection', $connection);
        
        // Add db.schema binding for schema operations
        $schema = Mockery::mock();
        $schema->shouldReceive('hasTable')->andReturn(true);
        $schema->shouldReceive('hasColumn')->andReturn(true);
        $schema->shouldReceive('getColumnListing')->andReturn([]);
        $schema->shouldReceive('getColumnType')->andReturn('string');
        $schema->shouldReceive('create')->andReturn(true);
        $schema->shouldReceive('drop')->andReturn(true);
        $schema->shouldReceive('dropIfExists')->andReturn(true);
        $schema->shouldReceive('table')->andReturnUsing(function ($table, $callback) {
            return $callback(Mockery::mock());
        });
        
        $this->app->instance('db.schema', $schema);
    }

    /**
     * Setup Hash facade mock
     */
    protected function setupHashMock(): void
    {
        $hash = Mockery::mock();
        $hash->shouldReceive('make')->andReturnUsing(function ($value) {
            return password_hash($value, PASSWORD_DEFAULT);
        });
        $hash->shouldReceive('check')->andReturnUsing(function ($value, $hash) {
            return password_verify($value, $hash);
        });

        $this->app->instance('hash', $hash);
        $this->app->instance(\Illuminate\Contracts\Hashing\Hasher::class, $hash);
    }

    /**
     * Setup Spatie Permission package
     */
    protected function setupSpatiePermission(): void
    {
        // Mock the Permission Registrar
        $registrar = Mockery::mock(\Spatie\Permission\PermissionRegistrar::class);
        $registrar->shouldReceive('getCacheKey')->andReturn('spatie.permission.cache');
        $registrar->shouldReceive('forgetCachedPermissions')->andReturn(true);
        $registrar->shouldReceive('getPermissions')->andReturn(collect([]));
        $registrar->shouldReceive('getRoles')->andReturn(collect([]));
        $registrar->shouldReceive('getPermissionClass')->andReturn('App\Models\Authority\Permission');
        $registrar->shouldReceive('getRoleClass')->andReturn('App\Models\Authority\Role');

        $this->app->instance(\Spatie\Permission\PermissionRegistrar::class, $registrar);
        $this->app->instance('permission.registrar', $registrar);

        // Mock Gate
        $gate = Mockery::mock(\Illuminate\Contracts\Auth\Access\Gate::class);
        $gate->shouldReceive('define')->andReturn(true);
        $gate->shouldReceive('before')->andReturn(true);
        $gate->shouldReceive('after')->andReturn(true);
        
        $this->app->instance(\Illuminate\Contracts\Auth\Access\Gate::class, $gate);
        $this->app->instance('gate', $gate);

        // Mock Cache for permissions
        $cache = Mockery::mock();
        $cache->shouldReceive('get')->andReturn(null);
        $cache->shouldReceive('put')->andReturn(true);
        $cache->shouldReceive('forget')->andReturn(true);
        $cache->shouldReceive('tags')->andReturnSelf();
        $cache->shouldReceive('flush')->andReturn(true);

        $cacheManager = Mockery::mock();
        $cacheManager->shouldReceive('store')->andReturn($cache);
        $cacheManager->shouldReceive('driver')->andReturn($cache);

        $this->app->instance('cache', $cacheManager);
        $this->app->instance(\Illuminate\Contracts\Cache\Repository::class, $cache);
    }

    /**
     * Create a mock for a class
     */
    protected function mock(string $class): MockInterface
    {
        if (!class_exists($class) && !interface_exists($class)) {
            throw new \InvalidArgumentException("Class or interface '{$class}' does not exist");
        }
        
        return Mockery::mock($class);
    }

    /**
     * Create a partial mock for a class
     */
    protected function partialMock(string $class, array $methods = []): MockInterface
    {
        $mock = Mockery::mock($class)->makePartial();

        foreach ($methods as $method => $return) {
            if (is_string($method)) {
                $mock->shouldReceive($method)->andReturn($return);
            } else {
                $mock->shouldReceive($return)->andReturnSelf();
            }
        }

        return $mock;
    }

    /**
     * Create a spy for a class
     */
    protected function spy(string $class): MockInterface
    {
        return Mockery::spy($class);
    }

    /**
     * Assert UUID format and structure
     */
    protected function assertValidUuid(string $uuid): void
    {
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $uuid,
            'Invalid UUID format'
        );
    }

    /**
     * Assert that array contains expected keys
     */
    protected function assertArrayContainsKeys(array $expectedKeys, array $actual): void
    {
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $actual, "Missing key: {$key}");
        }
    }

    /**
     * Generate test UUID (RFC 4122 v4 compliant)
     */
    protected function generateTestUuid(): string
    {
        $data = random_bytes(16);
        
        // Set version (4) and variant bits
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // Version 4
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // Variant 10
        
        return sprintf(
            '%08s-%04s-%04s-%04s-%12s',
            bin2hex(substr($data, 0, 4)),
            bin2hex(substr($data, 4, 2)),
            bin2hex(substr($data, 6, 2)),
            bin2hex(substr($data, 8, 2)),
            bin2hex(substr($data, 10, 6))
        );
    }

    /**
     * Create test data array with default values
     */
    protected function createTestData(array $overrides = []): array
    {
        $defaults = [
            'uuid' => $this->generateTestUuid(),
            'name' => 'Test Name',
            'created_at' => (new \DateTime())->format(\DateTimeInterface::ISO8601),
            'updated_at' => (new \DateTime())->format(\DateTimeInterface::ISO8601),
        ];

        return [...$defaults, ...$overrides];
    }

    /**
     * Mock configuration value
     */
    protected function mockConfig(string $key, $value): void
    {
        $config = Mockery::mock();
        $config->shouldReceive('get')
            ->withArgs(['app.locale'])
            ->andReturn('en');
        $config->shouldReceive('get')
            ->withArgs(['app.faker_locale', null])
            ->andReturn('en_US');
        $config->shouldReceive('get')
            ->with($key, Mockery::any())
            ->andReturn($value);
        $config->shouldReceive('get')
            ->andReturnUsing(function ($getKey, $default = null) use ($key, $value) {
                if ($getKey === $key) {
                    return $value;
                }
                return $default;
            });

        $this->app->instance('config', $config);
    }

    /**
     * Clean up after each test
     */
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}