<?php

use Illuminate\Support\Facades\Route;
use Modules\Taxi\Http\Controllers\Api\TaxiController;
use Modules\Taxi\Http\Controllers\Api\TaxiRideController;
use Modules\Taxi\Http\Controllers\Api\TaxiSafetyController;
use Modules\Taxi\Http\Controllers\Api\TaxiServiceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group.
|
*/

// Taxi module routes
Route::group(['prefix' => 'taxi'], function () {
    // Public routes
    Route::get('vehicle-types', [TaxiController::class, 'getVehicleTypes']);
    Route::post('estimate-fare', [TaxiController::class, 'estimateFare']);
    Route::get('coupon/list', [TaxiController::class, 'getCoupons']); // Public - guest can view coupons
    Route::post('coupon/apply', [TaxiController::class, 'applyCoupon']); // Public - guest can validate coupon
    Route::get('destinations', [TaxiController::class, 'getDestinations']);
    // User routes (authenticated)
    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('nearby-drivers', [TaxiController::class, 'getNearbyDrivers']);
        Route::post('request-ride', [TaxiController::class, 'requestRide']);
        Route::get('ride/{id}', [TaxiController::class, 'getRide']);
        Route::post('ride/{id}/cancel', [TaxiController::class, 'cancelRide']);
        Route::post('ride/{id}/apply-admin-incentive', [TaxiController::class, 'applyAdminIncentive']);
        Route::get('ride/{id}/tracking', [TaxiRideController::class, 'tracking']);
        Route::get('ride/{id}/details', [TaxiRideController::class, 'show']);
        Route::post('ride/{id}/rate', [TaxiController::class, 'rateRide']);
        Route::post('rate-ride', [TaxiController::class, 'rateRide']);
        Route::get('history', [TaxiController::class, 'history']);
        Route::get('ride-history', [TaxiController::class, 'history']);
        Route::get('current-ride', [TaxiController::class, 'getCurrentRide']);

        // Anti-fraud telemetry, dynamic destination & passenger completion
        Route::post('ride/{id}/passenger-telemetry', [TaxiController::class, 'passengerTelemetry']);
        Route::post('ride/{id}/edit-destination', [TaxiController::class, 'editDestination']);
        Route::post('ride/{id}/keep-original-destination', [TaxiController::class, 'keepOriginalDestination']);
        Route::post('ride/{id}/safe-dropoff-finish', [TaxiController::class, 'safeDropoffFinish']);
        Route::post('ride/{id}/passenger-complete', [TaxiController::class, 'passengerCompleteRide']);

        // DEBUG ONLY: Simulate driver acceptance
        Route::post('ride/{id}/debug-accept', [TaxiController::class, 'debugAcceptRide']);

        // Safety / SOS routes
        Route::group(['prefix' => 'safety'], function () {
            // Alerts
            Route::post('ride/{id}/insecure', [TaxiSafetyController::class, 'sendInsecureAlert']);
            Route::post('ride/{id}/emergency', [TaxiSafetyController::class, 'sendEmergencyAlert']);

            // Audio recordings
            Route::post('ride/{id}/recording', [TaxiSafetyController::class, 'uploadRecording']);

            // Share ride
            Route::post('ride/{id}/share-token', [TaxiSafetyController::class, 'generateShareToken']);

            // Emergency contacts management
            Route::get('contacts', [TaxiSafetyController::class, 'getEmergencyContacts']);
            Route::post('contacts', [TaxiSafetyController::class, 'addEmergencyContact']);
            Route::put('contacts/{id}', [TaxiSafetyController::class, 'updateEmergencyContact']);
            Route::delete('contacts/{id}', [TaxiSafetyController::class, 'deleteEmergencyContact']);
        });

        // Carpool Comunitario (Pasajeros y Conductores Universitarios)
        Route::group(['prefix' => 'carpool'], function () {
            Route::post('verify-community', [\Modules\Taxi\Http\Controllers\Api\TaxiCarpoolController::class, 'verifyCommunity']);
            Route::get('verification-status', [\Modules\Taxi\Http\Controllers\Api\TaxiCarpoolController::class, 'getVerificationStatus']);
            Route::post('routes/book', [\Modules\Taxi\Http\Controllers\Api\TaxiCarpoolController::class, 'bookRoute']);
            Route::get('my-bookings', [\Modules\Taxi\Http\Controllers\Api\TaxiCarpoolController::class, 'getMyBookings']);
            Route::post('bookings/{id}/cancel', [\Modules\Taxi\Http\Controllers\Api\TaxiCarpoolController::class, 'cancelBooking']);

            // Creación y gestión de rutas (Tengo Auto / Ofrezco Ride)
            Route::post('routes', [\Modules\Taxi\Http\Controllers\Api\TaxiCarpoolController::class, 'createRoute']);
            Route::get('my-published-routes', [\Modules\Taxi\Http\Controllers\Api\TaxiCarpoolController::class, 'getMyPublishedRoutes']);

            // Creación y consulta de solicitudes (Busco Ride / Pasajero)
            Route::post('requests', [\Modules\Taxi\Http\Controllers\Api\TaxiCarpoolController::class, 'createRequest']);
            Route::get('requests', [\Modules\Taxi\Http\Controllers\Api\TaxiCarpoolController::class, 'getRequests']);
            Route::get('my-requests', [\Modules\Taxi\Http\Controllers\Api\TaxiCarpoolController::class, 'getMyRequests']);
            Route::post('requests/{id}/offer', [\Modules\Taxi\Http\Controllers\Api\TaxiCarpoolController::class, 'respondToRequest']);
            Route::delete('requests/{id}', [\Modules\Taxi\Http\Controllers\Api\TaxiCarpoolController::class, 'deleteRequest']);
        });
    });

    // Public: Track shared ride (no auth required)
    Route::get('track/{token}', [TaxiSafetyController::class, 'getSharedRideTracking']);

    // Carpool Comunitario (Rutas públicas)
    Route::get('carpool/organizations', [\Modules\Taxi\Http\Controllers\Api\TaxiCarpoolController::class, 'getOrganizations']);
    Route::get('carpool/routes/search', [\Modules\Taxi\Http\Controllers\Api\TaxiCarpoolController::class, 'searchRoutes']);
});
