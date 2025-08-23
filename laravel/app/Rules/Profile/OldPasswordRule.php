<?php

namespace App\Rules\Profile;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Hash;

class OldPasswordRule implements ValidationRule
{
    public function __construct(private ?object $user = null)
    {
        $this->user = $this->user ?? auth()->user();
    }

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
