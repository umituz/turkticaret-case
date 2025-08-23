<?php

use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Order\OrderStatusController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'index']);
    Route::post('/', [OrderController::class, 'store']);
    Route::get('/{order}', [OrderController::class, 'show']);
    
    Route::prefix('{order}/status')->group(function () {
        Route::patch('/', [OrderStatusController::class, 'update']);
        Route::get('/history', [OrderStatusController::class, 'getStatusHistory']);
    });
});