<?php

namespace App\Rules\Profile;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validation rule to ensure at least one of the specified fields is provided.
 * 
 * This rule is useful for update operations where you want to ensure
 * that at least one field is provided before processing the request.
 * Commonly used in profile update scenarios.
 *
 * @package App\Rules\Profile
 */
class AtLeastOneFieldRule implements ValidationRule
{
    /**
     * Create a new rule instance.
     *
     * @param array $fields Array of field names to check
     */
    public function __construct(protected array $fields) {}

    /**
     * Run the validation rule.
     * 
     * Checks if at least one of the specified fields has a value
     * in the current request.
     *
     * @param string $attribute The attribute being validated
     * @param mixed $value The value of the attribute
     * @param Closure $fail Callback to call if validation fails
     * @return void
     */
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