<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class SecureFileUpload implements ValidationRule
{
    private array $allowedMimeTypes;
    private int $maxFileSize;
    private array $allowedExtensions;

    public function __construct(
        array $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'],
        int $maxFileSize = 5 * 1024 * 1024, // 5MB default
        array $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp']
    ) {
        $this->allowedMimeTypes = $allowedMimeTypes;
        $this->maxFileSize = $maxFileSize;
        $this->allowedExtensions = $allowedExtensions;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value instanceof UploadedFile) {
            $fail('The file upload is invalid.');
            return;
        }

        // Check file size
        if ($value->getSize() > $this->maxFileSize) {
            $fail("The file size exceeds the maximum allowed size of " . ($this->maxFileSize / 1024 / 1024) . "MB.");
            return;
        }

        // Check MIME type
        $mimeType = $value->getMimeType();
        if (!in_array($mimeType, $this->allowedMimeTypes)) {
            $fail('The file type is not allowed. Allowed types: ' . implode(', ', $this->allowedMimeTypes));
            return;
        }

        // Check file extension
        $extension = strtolower($value->getClientOriginalExtension());
        if (!in_array($extension, $this->allowedExtensions)) {
            $fail('The file extension is not allowed. Allowed extensions: ' . implode(', ', $this->allowedExtensions));
            return;
        }

        // Additional security checks
        $this->performSecurityChecks($value, $fail);
    }

    private function performSecurityChecks(UploadedFile $file, Closure $fail): void
    {
        // Check for executable file signatures
        $handle = fopen($file->getRealPath(), 'rb');
        if (!$handle) {
            $fail('Unable to read file for security validation.');
            return;
        }

        $header = fread($handle, 8);
        fclose($handle);

        // Check for common executable file signatures
        $dangerousSignatures = [
            "\x4D\x5A", // PE/COFF executable
            "\x7F\x45\x4C\x46", // ELF executable
            "\xFE\xED\xFA\xCE", // Mach-O binary (32-bit)
            "\xFE\xED\xFA\xCF", // Mach-O binary (64-bit)
        ];

        foreach ($dangerousSignatures as $signature) {
            if (str_starts_with($header, $signature)) {
                $fail('The uploaded file contains potentially dangerous content.');
                return;
            }
        }

        // Check filename for dangerous characters
        $filename = $file->getClientOriginalName();
        if (preg_match('/[<>:"|?*\x00-\x1F\x7F]/', $filename)) {
            $fail('The filename contains invalid characters.');
            return;
        }

        // Check for double extensions (like .jpg.exe)
        if (preg_match('/\.[a-zA-Z0-9]{1,4}\.[a-zA-Z0-9]{1,4}$/', $filename)) {
            $fail('Double file extensions are not allowed.');
            return;
        }
    }
}