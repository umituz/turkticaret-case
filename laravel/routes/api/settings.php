<?php

use App\Http\Controllers\Setting\SettingsController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/settings')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [SettingsController::class, 'index'])->name('admin.settings.index');
    Route::put('/', [SettingsController::class, 'update'])->name('admin.settings.update');
    Route::get('/status', [SettingsController::class, 'status'])->name('admin.settings.status');
});