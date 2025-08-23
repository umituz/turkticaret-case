<?php

use App\Http\Controllers\User\Profile\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('profile')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [ProfileController::class, 'show']);
    Route::put('/', [ProfileController::class, 'update']);
});
