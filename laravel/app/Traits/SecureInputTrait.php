<?php

namespace App\Traits;

trait SecureInputTrait
{
    /**
     * Sanitize string input to prevent XSS attacks
     */
    protected function sanitizeString(string $input): string
    {
        // Remove HTML tags and encode special characters
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize and escape LIKE query wildcards
     */
    protected function escapeLikeWildcards(string $input): string
    {
        return str_replace(['%', '_', '\\'], ['\\%', '\\_', '\\\\'], $input);
    }

    /**
     * Validate and sanitize email input
     */
    protected function sanitizeEmail(string $email): string
    {
        return strtolower(trim(filter_var($email, FILTER_SANITIZE_EMAIL)));
    }

    /**
     * Sanitize numeric input
     */
    protected function sanitizeNumeric(mixed $input): int|float|null
    {
        if (!is_numeric($input)) {
            return null;
        }
        
        return is_float($input + 0) ? (float) $input : (int) $input;
    }

    /**
     * Sanitize boolean input
     */
    protected function sanitizeBoolean(mixed $input): bool
    {
        return filter_var($input, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
    }

    /**
     * Clean array input recursively
     */
    protected function sanitizeArray(array $input): array
    {
        $cleaned = [];
        
        foreach ($input as $key => $value) {
            $cleanKey = $this->sanitizeString((string) $key);
            
            if (is_array($value)) {
                $cleaned[$cleanKey] = $this->sanitizeArray($value);
            } elseif (is_string($value)) {
                $cleaned[$cleanKey] = $this->sanitizeString($value);
            } else {
                $cleaned[$cleanKey] = $value;
            }
        }
        
        return $cleaned;
    }
}