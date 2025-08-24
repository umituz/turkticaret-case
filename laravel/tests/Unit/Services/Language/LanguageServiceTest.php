<?php

namespace Tests\Unit\Services\Language;

use App\Services\Language\LanguageService;
use App\Repositories\Language\LanguageRepositoryInterface;
use App\Models\Language\Language;
use App\Helpers\LanguageHelper;
use Tests\Base\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Mockery;
use Mockery\MockInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Unit tests for LanguageService
 * Tests language management operations with repository mocking
 */
#[CoversClass(LanguageService::class)]
#[Group('unit')]
#[Group('services')]
#[Small]
class LanguageServiceTest extends UnitTestCase
{
    private LanguageService $service;
    private LanguageRepositoryInterface|MockInterface $languageRepository;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->languageRepository = Mockery::mock(LanguageRepositoryInterface::class);
        $this->service = new LanguageService($this->languageRepository);
    }

    #[Test]
    public function get_all_languages_returns_collection_from_repository(): void
    {
        // Arrange
        $expectedCollection = Mockery::mock(Collection::class);
        $this->languageRepository
            ->shouldReceive('all')
            ->once()
            ->andReturn($expectedCollection);

        // Act
        $result = $this->service->getAllLanguages();

        // Assert
        $this->assertSame($expectedCollection, $result);
    }

    #[Test]
    public function create_language_creates_language_through_repository(): void
    {
        // Arrange
        $languageData = [
            'name' => 'Turkish',
            'code' => 'tr',
            'iso_code' => 'tr-TR',
            'is_rtl' => false
        ];
        $expectedLanguage = Mockery::mock(Language::class);

        $this->languageRepository
            ->shouldReceive('create')
            ->once()
            ->with($languageData)
            ->andReturn($expectedLanguage);

        // Act
        $result = $this->service->createLanguage($languageData);

        // Assert
        $this->assertSame($expectedLanguage, $result);
    }

    #[Test]
    public function update_language_updates_language_by_uuid(): void
    {
        // Arrange
        $uuid = 'language-uuid';
        $updateData = [
            'name' => 'Türkçe',
            'is_rtl' => false
        ];
        $expectedLanguage = Mockery::mock(Language::class);

        $this->languageRepository
            ->shouldReceive('updateByUuid')
            ->once()
            ->with($uuid, $updateData)
            ->andReturn($expectedLanguage);

        // Act
        $result = $this->service->updateLanguage($uuid, $updateData);

        // Assert
        $this->assertSame($expectedLanguage, $result);
    }

    #[Test]
    public function delete_language_deletes_language_by_uuid(): void
    {
        // Arrange
        $uuid = 'language-uuid';
        
        $this->languageRepository
            ->shouldReceive('deleteByUuid')
            ->once()
            ->with($uuid)
            ->andReturn(true);

        // Act
        $result = $this->service->deleteLanguage($uuid);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function get_by_country_locale_method_exists_and_calls_repository(): void
    {
        // Arrange - We can't easily mock static methods, but we can test the method exists
        // and throws expected exceptions for non-existent languages
        
        $this->languageRepository
            ->shouldReceive('findByCode')
            ->once()
            ->with(Mockery::type('string'))
            ->andReturn(null);

        // Act & Assert - The method should throw exception when language not found
        $this->expectException(\InvalidArgumentException::class);
        
        // This will fail at the repository level since we're returning null
        $this->service->getByCountryLocale('invalid-locale');
    }

    #[Test]
    public function delete_language_returns_false_when_deletion_fails(): void
    {
        // Arrange
        $uuid = 'non-existent-uuid';
        
        $this->languageRepository
            ->shouldReceive('deleteByUuid')
            ->once()
            ->with($uuid)
            ->andReturn(false);

        // Act
        $result = $this->service->deleteLanguage($uuid);

        // Assert
        $this->assertFalse($result);
    }
}