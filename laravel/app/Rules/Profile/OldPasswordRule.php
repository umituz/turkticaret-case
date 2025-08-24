<?php

namespace App\Rules\Profile;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Hash;

/**
 * Validation rule to verify the user's current password.
 * 
 * This rule is used when changing passwords to ensure the user
 * provides their correct current password before allowing the change.
 * Enhances security by requiring password confirmation.
 *
 * @package App\Rules\Profile
 */
class OldPasswordRule implements ValidationRule
{
    /**
     * Create a new rule instance.
     *
     * @param object|null $user The user to verify password against (defaults to authenticated user)
     */
    public function __construct(private ?object $user = null)
    {
        $this->user = $this->user ?? auth()->user();
    }

    /**
     * Run the validation rule.
     * 
     * Verifies that the provided password matches the user's current password
     * using Laravel's Hash facade for secure comparison.
     *
     * @param string $attribute The attribute being validated
     * @param mixed $value The password value to verify
     * @param Closure $fail Callback to call if validation fails
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->user) {
            $fail('Authentication required.');
            return;
        }

        if (!Hash::check($value, $this->user->password)) {
            $fail('The old password is incorrect.');
        }
    }
}
