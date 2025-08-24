<?php

namespace Tests\Unit\Rules;

use App\Rules\SecureFileUpload;
use Tests\Base\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Illuminate\Http\UploadedFile;
use Mockery;

/**
 * Unit tests for SecureFileUpload validation rule
 * Tests file upload security validation
 */
#[CoversClass(SecureFileUpload::class)]
#[Group('unit')]
#[Group('rules')]
#[Small]
class SecureFileUploadTest extends UnitTestCase
{
    #[Test]
    public function validate_fails_when_value_is_not_uploaded_file(): void
    {
        // Arrange
        $rule = new SecureFileUpload();
        $attribute = 'file';
        $value = 'not-a-file';
        $failCalled = false;
        $failMessage = '';
        
        $fail = function($message) use (&$failCalled, &$failMessage) {
            $failCalled = true;
            $failMessage = $message;
        };

        // Act
        $rule->validate($attribute, $value, $fail);

        // Assert
        $this->assertTrue($failCalled);
        $this->assertEquals('The file upload is invalid.', $failMessage);
    }

    #[Test]
    public function validate_fails_when_file_exceeds_max_size(): void
    {
        // Arrange
        $maxSize = 1024; // 1KB
        $rule = new SecureFileUpload(['image/jpeg'], $maxSize, ['jpg']);
        $attribute = 'file';
        
        $file = Mockery::mock(UploadedFile::class);
        $file->shouldReceive('getSize')
            ->once()
            ->andReturn(2048); // 2KB - exceeds limit
            
        $failCalled = false;
        $failMessage = '';
        
        $fail = function($message) use (&$failCalled, &$failMessage) {
            $failCalled = true;
            $failMessage = $message;
        };

        // Act
        $rule->validate($attribute, $file, $fail);

        // Assert
        $this->assertTrue($failCalled);
        $this->assertStringContainsString('file size exceeds', $failMessage);
    }

    #[Test]
    public function validate_fails_when_mime_type_not_allowed(): void
    {
        // Arrange
        $rule = new SecureFileUpload(['image/jpeg'], 5 * 1024 * 1024, ['jpg']);
        $attribute = 'file';
        
        $file = Mockery::mock(UploadedFile::class);
        $file->shouldReceive('getSize')
            ->once()
            ->andReturn(1024);
        $file->shouldReceive('getMimeType')
            ->once()
            ->andReturn('image/png'); // Not allowed
            
        $failCalled = false;
        $failMessage = '';
        
        $fail = function($message) use (&$failCalled, &$failMessage) {
            $failCalled = true;
            $failMessage = $message;
        };

        // Act
        $rule->validate($attribute, $file, $fail);

        // Assert
        $this->assertTrue($failCalled);
        $this->assertStringContainsString('file type is not allowed', $failMessage);
    }

    #[Test]
    public function validate_fails_when_extension_not_allowed(): void
    {
        // Arrange
        $rule = new SecureFileUpload(['image/jpeg'], 5 * 1024 * 1024, ['jpg']);
        $attribute = 'file';
        
        $file = Mockery::mock(UploadedFile::class);
        $file->shouldReceive('getSize')
            ->once()
            ->andReturn(1024);
        $file->shouldReceive('getMimeType')
            ->once()
            ->andReturn('image/jpeg');
        $file->shouldReceive('getClientOriginalExtension')
            ->once()
            ->andReturn('png'); // Not allowed
            
        $failCalled = false;
        $failMessage = '';
        
        $fail = function($message) use (&$failCalled, &$failMessage) {
            $failCalled = true;
            $failMessage = $message;
        };

        // Act
        $rule->validate($attribute, $file, $fail);

        // Assert
        $this->assertTrue($failCalled);
        $this->assertStringContainsString('file extension is not allowed', $failMessage);
    }

    #[Test]
    public function validate_passes_with_valid_file(): void
    {
        // Arrange
        $rule = new SecureFileUpload(['image/jpeg'], 5 * 1024 * 1024, ['jpg']);
        $attribute = 'file';
        
        // Create a temporary file with safe content
        $tempFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tempFile, 'safe image content');
        
        $file = Mockery::mock(UploadedFile::class);
        $file->shouldReceive('getSize')
            ->once()
            ->andReturn(1024);
        $file->shouldReceive('getMimeType')
            ->once()
            ->andReturn('image/jpeg');
        $file->shouldReceive('getClientOriginalExtension')
            ->once()
            ->andReturn('jpg');
        $file->shouldReceive('getRealPath')
            ->once()
            ->andReturn($tempFile);
        $file->shouldReceive('getClientOriginalName')
            ->once()
            ->andReturn('image.jpg');
            
        $failCalled = false;
        
        $fail = function($message) use (&$failCalled) {
            $failCalled = true;
        };

        // Act
        $rule->validate($attribute, $file, $fail);

        // Assert
        $this->assertFalse($failCalled);
        
        // Cleanup
        unlink($tempFile);
    }

    #[Test]
    public function validate_fails_with_dangerous_filename_characters(): void
    {
        // Arrange
        $rule = new SecureFileUpload(['image/jpeg'], 5 * 1024 * 1024, ['jpg']);
        $attribute = 'file';
        
        $tempFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tempFile, 'safe content');
        
        $file = Mockery::mock(UploadedFile::class);
        $file->shouldReceive('getSize')
            ->once()
            ->andReturn(1024);
        $file->shouldReceive('getMimeType')
            ->once()
            ->andReturn('image/jpeg');
        $file->shouldReceive('getClientOriginalExtension')
            ->once()
            ->andReturn('jpg');
        $file->shouldReceive('getRealPath')
            ->once()
            ->andReturn($tempFile);
        $file->shouldReceive('getClientOriginalName')
            ->once()
            ->andReturn('image<script>.jpg'); // Dangerous filename
            
        $failCalled = false;
        $failMessage = '';
        
        $fail = function($message) use (&$failCalled, &$failMessage) {
            $failCalled = true;
            $failMessage = $message;
        };

        // Act
        $rule->validate($attribute, $file, $fail);

        // Assert
        $this->assertTrue($failCalled);
        $this->assertStringContainsString('filename contains invalid characters', $failMessage);
        
        // Cleanup
        unlink($tempFile);
    }

    #[Test]
    public function validate_fails_with_double_extensions(): void
    {
        // Arrange
        $rule = new SecureFileUpload(['image/jpeg'], 5 * 1024 * 1024, ['jpg']);
        $attribute = 'file';
        
        $tempFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tempFile, 'safe content');
        
        $file = Mockery::mock(UploadedFile::class);
        $file->shouldReceive('getSize')
            ->once()
            ->andReturn(1024);
        $file->shouldReceive('getMimeType')
            ->once()
            ->andReturn('image/jpeg');
        $file->shouldReceive('getClientOriginalExtension')
            ->once()
            ->andReturn('jpg');
        $file->shouldReceive('getRealPath')
            ->once()
            ->andReturn($tempFile);
        $file->shouldReceive('getClientOriginalName')
            ->once()
            ->andReturn('image.jpg.exe'); // Double extension
            
        $failCalled = false;
        $failMessage = '';
        
        $fail = function($message) use (&$failCalled, &$failMessage) {
            $failCalled = true;
            $failMessage = $message;
        };

        // Act
        $rule->validate($attribute, $file, $fail);

        // Assert
        $this->assertTrue($failCalled);
        $this->assertStringContainsString('Double file extensions are not allowed', $failMessage);
        
        // Cleanup
        unlink($tempFile);
    }

    #[Test]
    public function constructor_accepts_custom_parameters(): void
    {
        // Arrange & Act
        $allowedMimeTypes = ['application/pdf'];
        $maxFileSize = 10 * 1024 * 1024; // 10MB
        $allowedExtensions = ['pdf'];
        
        $rule = new SecureFileUpload($allowedMimeTypes, $maxFileSize, $allowedExtensions);

        // Assert - Test that custom parameters are used
        $attribute = 'file';
        $file = Mockery::mock(UploadedFile::class);
        $file->shouldReceive('getSize')
            ->once()
            ->andReturn(1024);
        $file->shouldReceive('getMimeType')
            ->once()
            ->andReturn('application/pdf');
        $file->shouldReceive('getClientOriginalExtension')
            ->once()
            ->andReturn('pdf');
        $file->shouldReceive('getRealPath')
            ->once()
            ->andReturn(__FILE__); // Use current file as test
        $file->shouldReceive('getClientOriginalName')
            ->once()
            ->andReturn('document.pdf');
            
        $failCalled = false;
        $fail = function($message) use (&$failCalled) {
            $failCalled = true;
        };

        $rule->validate($attribute, $file, $fail);
        
        $this->assertFalse($failCalled);
    }
}