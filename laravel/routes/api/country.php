<?php

use App\Http\Controllers\Country\CountryController;
use Illuminate\Support\Facades\Route;

Route::prefix('countries')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [CountryController::class, 'index']);
    Route::get('/{country}', [CountryController::class, 'show']);
    Route::post('/', [CountryController::class, 'store']);
    Route::put('/{country}', [CountryController::class, 'update']);
    Route::delete('/{country}', [CountryController::class, 'destroy']);
});
