<?php

use Illuminate\Support\Facades\Route;
use Modules\Espacios\Http\Controllers\Api\EspaciosController;
use Modules\Espacios\Http\Controllers\Api\EspaciosBookingController;
use Modules\Espacios\Http\Controllers\Api\EspaciosHostController;
use Modules\Espacios\Http\Controllers\Api\EspaciosReviewController;

/*
|--------------------------------------------------------------------------
| Espacios API Routes
|--------------------------------------------------------------------------
|
| Todas las rutas del módulo de renta de espacios.
| Prefijo base: api/v1/espacios
|
*/

Route::group(['prefix' => 'espacios'], function () {

    // ——————————————————————————————
    // Rutas públicas (sin autenticación)
    // ——————————————————————————————
    Route::get('types', [EspaciosController::class, 'getTypes']);
    Route::get('amenities', [EspaciosController::class, 'getAmenities']);
    Route::get('listings', [EspaciosController::class, 'index']);
    Route::get('listings/featured', [EspaciosController::class, 'featured']);
    Route::get('listings/{id}', [EspaciosController::class, 'show']);
    Route::get('listings/{id}/reviews', [EspaciosReviewController::class, 'index']);

    // ——————————————————————————————
    // Rutas autenticadas (huésped)
    // ——————————————————————————————
    Route::group(['middleware' => 'auth:api'], function () {

        // Reservas del huésped
        Route::get('bookings', [EspaciosBookingController::class, 'index']);
        Route::post('bookings', [EspaciosBookingController::class, 'store']);
        Route::get('bookings/{id}', [EspaciosBookingController::class, 'show']);
        Route::post('bookings/{id}/cancel', [EspaciosBookingController::class, 'cancel']);
        Route::post('bookings/{id}/review', [EspaciosReviewController::class, 'store']);

        // ——————————————————————————————
        // Rutas del anfitrión (host)
        // ——————————————————————————————
        Route::group(['prefix' => 'host'], function () {
            // Espacios del host
            Route::get('listings', [EspaciosHostController::class, 'index']);
            Route::post('listings', [EspaciosHostController::class, 'store']);
            Route::get('listings/{id}', [EspaciosHostController::class, 'show']);
            Route::put('listings/{id}', [EspaciosHostController::class, 'update']);
            Route::delete('listings/{id}', [EspaciosHostController::class, 'destroy']);

            // Gestión de reservas recibidas
            Route::get('bookings', [EspaciosHostController::class, 'hostBookings']);
            Route::post('bookings/{id}/confirm', [EspaciosHostController::class, 'confirmBooking']);
            Route::post('bookings/{id}/reject', [EspaciosHostController::class, 'rejectBooking']);
        });
    });
});
