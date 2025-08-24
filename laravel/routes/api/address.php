<?php

use App\Http\Controllers\User\Address\AddressController;
use Illuminate\Support\Facades\Route;

Route::prefix('addresses')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [AddressController::class, 'index']);
    Route::post('/', [AddressController::class, 'store']);
    Route::get('/{userAddress}', [AddressController::class, 'show']);
    Route::put('/{userAddress}', [AddressController::class, 'update']);
    Route::delete('/{userAddress}', [AddressController::class, 'destroy']);
});