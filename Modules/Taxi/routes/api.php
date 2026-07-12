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
    Route::get('db-test', function () {
        try {
            \Illuminate\Support\Facades\DB::connection()->getPdo();
            return response()->json([
                'status' => 'success',
                'columns' => \Illuminate\Support\Facades\Schema::getColumnListing('taxi_rides')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    });
    Route::get('vehicle-types', [TaxiController::class, 'getVehicleTypes']);
    Route::post('estimate-fare', [TaxiController::class, 'estimateFare']);
    Route::get('coupon/list', [TaxiController::class, 'getCoupons']); // Public - guest can view coupons
    Route::post('coupon/apply', [TaxiController::class, 'applyCoupon']); // Public - guest can validate coupon

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
        Route::get('history', [TaxiController::class, 'history']);
        Route::get('current-ride', [TaxiController::class, 'getCurrentRide']);

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
    });

    // Public: Track shared ride (no auth required)
    Route::get('track/{token}', [TaxiSafetyController::class, 'getSharedRideTracking']);
});
