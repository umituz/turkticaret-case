<?php

use App\Http\Controllers\Currency\CurrencyController;
use Illuminate\Support\Facades\Route;

Route::prefix('currencies')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [CurrencyController::class, 'index']);
    Route::get('/{currency}', [CurrencyController::class, 'show']);
    Route::post('/', [CurrencyController::class, 'store']);
    Route::put('/{currency}', [CurrencyController::class, 'update']);
    Route::delete('/{currency}', [CurrencyController::class, 'destroy']);
});
