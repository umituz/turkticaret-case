<?php

use App\Http\Controllers\Shipping\ShippingController;
use Illuminate\Support\Facades\Route;

Route::prefix('shipping')->group(function () {
    Route::get('/methods', [ShippingController::class, 'getMethods']);
});