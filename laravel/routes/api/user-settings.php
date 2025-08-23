<?php

use App\Http\Controllers\User\UserSettings\UserSettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->prefix('user/settings')->group(function () {
    Route::get('/', [UserSettingsController::class, 'getUserSettings']);
    Route::post('/', [UserSettingsController::class, 'createDefaultSettings']);
    Route::put('/notifications', [UserSettingsController::class, 'updateNotifications']);
    Route::put('/preferences', [UserSettingsController::class, 'updatePreferences']);
    Route::put('/password', [UserSettingsController::class, 'changePassword']);
});
