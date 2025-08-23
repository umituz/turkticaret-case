<?php

use App\Http\Controllers\Language\LanguageController;
use Illuminate\Support\Facades\Route;

Route::get('/languages', [LanguageController::class, 'index']);

Route::prefix('languages')->middleware('auth:sanctum')->group(function () {
    Route::get('/{language}', [LanguageController::class, 'show']);
    Route::post('/', [LanguageController::class, 'store']);
    Route::put('/{language}', [LanguageController::class, 'update']);
    Route::delete('/{language}', [LanguageController::class, 'destroy']);
});
