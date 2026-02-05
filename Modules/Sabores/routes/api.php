<?php

use Illuminate\Support\Facades\Route;
use Modules\Sabores\App\Http\Controllers\Api\SaboresCiudadController;
use Modules\Sabores\App\Http\Middleware\ImageCacheMiddleware;

// Apply cache middleware to all routes for better performance
Route::middleware(['image.cache'])->group(function () {
    Route::get('/map/stores', [SaboresCiudadController::class, 'getStoresForMap']);
    Route::get('/stores/{id}', [SaboresCiudadController::class, 'getStoreDetails']);

    Route::middleware('auth:api')->group(function () {
        Route::post('/reservation', [SaboresCiudadController::class, 'createReservation']);
        Route::get('/reservations', [SaboresCiudadController::class, 'getUserReservations']);
        Route::put('/reservations/{id}', [SaboresCiudadController::class, 'updateReservation']);
        Route::put('/reservations/{id}/cancel', [SaboresCiudadController::class, 'cancelReservation']);
    });
});
