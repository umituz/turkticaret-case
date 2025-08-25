<?php

use App\Http\Controllers\Setting\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/settings', [SettingsController::class, 'index'])->name('admin.settings.index');

Route::prefix('admin/settings')->middleware(['auth:sanctum', 'role:Admin'])->group(function () {
    Route::put('/', [SettingsController::class, 'update'])->name('admin.settings.update');
    Route::get('/status', [SettingsController::class, 'status'])->name('admin.settings.status');
});