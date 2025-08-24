<?php

use App\Http\Controllers\Cart\CartController;
use Illuminate\Support\Facades\Route;

Route::prefix('cart')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [CartController::class, 'index']);
    Route::post('/add', [CartController::class, 'add']);
    Route::put('/update', [CartController::class, 'update']);
    Route::delete('/remove/{product_uuid}', [CartController::class, 'remove']);
    Route::delete('/clear', [CartController::class, 'clear']);
});