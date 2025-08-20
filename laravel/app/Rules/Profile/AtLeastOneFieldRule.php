<?php

namespace App\Rules\Profile;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AtLeastOneFieldRule implements ValidationRule
{
    public function __construct(protected array $fields) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $request = request();
        
        $hasAtLeastOneField = false;
        foreach ($this->fields as $field) {
            if ($request->filled($field)) {
                $hasAtLeastOneField = true;
                break;
            }
        }

        if (!$hasAtLeastOneField) {
            $fail('At least one field must be provided for update.');
        }
    }
}