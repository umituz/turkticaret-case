<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class AuthHelper
{
    public static function getUserUuid(): ?string
    {
        return Auth::user()?->uuid;
    }
}