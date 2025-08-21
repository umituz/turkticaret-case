<?php

use App\Http\Controllers\Category\CategoryController;
use Illuminate\Support\Facades\Route;

Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('/{category}', [CategoryController::class, 'show']);

    Route::middleware(['auth:sanctum', 'role:Admin'])->group(function () {
        Route::post('/', [CategoryController::class, 'store']);
        Route::put('/{category}', [CategoryController::class, 'update']);
        Route::delete('/{category}', [CategoryController::class, 'destroy']);
        Route::post('/{category}/restore', [CategoryController::class, 'restore'])->withTrashed();
        Route::delete('/{category}/force-delete', [CategoryController::class, 'forceDelete'])->withTrashed();
    });
});