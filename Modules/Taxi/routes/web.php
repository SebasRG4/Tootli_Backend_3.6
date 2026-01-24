<?php

use Illuminate\Support\Facades\Route;
use Modules\Taxi\Http\Controllers\Admin\TaxiManagementController;
use Modules\Taxi\Http\Controllers\Admin\TaxiDriverVerificationController;
use Modules\Taxi\Http\Controllers\Admin\TaxiSafetyController;
use Modules\Taxi\Http\Controllers\Admin\TaxiSimulatorController;
use Modules\Taxi\Models\TaxiRide;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['admin', 'current-module', 'actch:admin_panel']], function () {

    // Taxi Management Routes
    Route::group(['prefix' => 'taxi', 'as' => 'taxi.'], function () {
        Route::get('dashboard', [TaxiManagementController::class, 'dashboard'])->name('dashboard');

        // Drivers
        Route::get('drivers', [TaxiManagementController::class, 'drivers'])->name('drivers');
        Route::post('drivers/store', [TaxiManagementController::class, 'storeDriver'])->name('drivers.store');
        Route::put('drivers/update/{id}', [TaxiManagementController::class, 'updateDriver'])->name('drivers.update');
        Route::delete('drivers/delete/{id}', [TaxiManagementController::class, 'deleteDriver'])->name('drivers.delete');
        Route::get('drivers/toggle-verification/{id}', [TaxiManagementController::class, 'toggleDriverVerification'])->name('drivers.toggle-verification');
        Route::get('drivers/search-users', [TaxiManagementController::class, 'searchUsers'])->name('drivers.search-users');

        // Driver Verification
        Route::group(['prefix' => 'drivers/verification', 'as' => 'drivers.verification.'], function () {
            Route::get('/', [TaxiDriverVerificationController::class, 'index'])->name('index');
            Route::get('/{id}', [TaxiDriverVerificationController::class, 'show'])->name('show');
            Route::post('/{id}/update', [TaxiDriverVerificationController::class, 'updateStatus'])->name('update');
            Route::post('/{id}/documents', [TaxiDriverVerificationController::class, 'updateDocuments'])->name('update-documents');
        });

        // Vehicles
        Route::get('vehicles', [TaxiManagementController::class, 'vehicles'])->name('vehicles');
        Route::post('vehicles/store', [TaxiManagementController::class, 'storeVehicle'])->name('vehicles.store');
        Route::post('vehicles/update/{id}', [TaxiManagementController::class, 'updateVehicle'])->name('vehicles.update');
        Route::delete('vehicles/delete/{id}', [TaxiManagementController::class, 'deleteVehicle'])->name('vehicles.delete');

        // Fare Configuration
        Route::get('fare-config', [TaxiManagementController::class, 'fareConfig'])->name('fare-config');
        Route::post('fare-config/store', [TaxiManagementController::class, 'storeFareConfig'])->name('fare-config.store');
        Route::post('fare-config/update/{id}', [TaxiManagementController::class, 'updateFareConfig'])->name('fare-config.update');
        Route::delete('fare-config/delete/{id}', [TaxiManagementController::class, 'deleteFareConfig'])->name('fare-config.delete');

        // Vehicle Types
        Route::get('vehicle-types', [TaxiManagementController::class, 'vehicleTypes'])->name('vehicle-types');
        Route::post('vehicle-types/store', [TaxiManagementController::class, 'storeVehicleType'])->name('vehicle-types.store');
        Route::post('vehicle-types/update/{id}', [TaxiManagementController::class, 'updateVehicleType'])->name('vehicle-types.update');
        Route::delete('vehicle-types/delete/{id}', [TaxiManagementController::class, 'deleteVehicleType'])->name('vehicle-types.delete');

        // Rides
        Route::get('rides', [TaxiManagementController::class, 'rides'])->name('rides');
        Route::get('rides/{id}', [TaxiManagementController::class, 'rideDetails'])->name('rides.details');
        Route::post('rides/update-status/{id}', [TaxiManagementController::class, 'updateRideStatus'])->name('rides.update-status');

        // Taxi Simulator for testing (without driver app)
        Route::get('simulator', [TaxiSimulatorController::class, 'index'])->name('simulator');
        Route::post('simulator/trip/{trip_id}/accept', [TaxiSimulatorController::class, 'acceptTrip'])->name('simulator.accept');
        Route::post('simulator/trip/{trip_id}/update-location', [TaxiSimulatorController::class, 'updateDriverLocation'])->name('simulator.update-location');
        Route::post('simulator/trip/{trip_id}/simulate-movement', [TaxiSimulatorController::class, 'simulateMovement'])->name('simulator.simulate-movement');
        Route::post('simulator/trip/{trip_id}/change-status', [TaxiSimulatorController::class, 'changeStatus'])->name('simulator.change-status');
        Route::get('simulator/trip/{trip_id}', function ($trip_id) {
            return response()->json(['trip' => \Modules\Taxi\Models\TaxiRide::with(['user', 'driver'])->find($trip_id)]);
        })->name('simulator.get-trip');

        // Safety Alerts
        Route::group(['prefix' => 'safety', 'as' => 'safety.'], function () {
            Route::get('/', [TaxiSafetyController::class, 'index'])->name('index');
            Route::get('/{id}', [TaxiSafetyController::class, 'show'])->name('show');
            Route::post('/{id}/contact', [TaxiSafetyController::class, 'markContacted'])->name('contact');
            Route::post('/{id}/resolve', [TaxiSafetyController::class, 'markResolved'])->name('resolve');
            Route::post('/{id}/escalate', [TaxiSafetyController::class, 'escalate'])->name('escalate');
            Route::get('/api/pending', [TaxiSafetyController::class, 'getPendingAlerts'])->name('pending');
            Route::get('/{id}/report', [TaxiSafetyController::class, 'generateReport'])->name('report');
            Route::get('/recordings/list', [TaxiSafetyController::class, 'recordings'])->name('recordings');
        });

        // Coupons
        Route::group(['prefix' => 'coupons', 'as' => 'coupons.'], function () {
            Route::get('/', [TaxiManagementController::class, 'coupons'])->name('index');
            Route::get('/create', [TaxiManagementController::class, 'createCoupon'])->name('create');
            Route::post('/store', [TaxiManagementController::class, 'storeCoupon'])->name('store');
            Route::get('/edit/{id}', [TaxiManagementController::class, 'editCoupon'])->name('edit');
            Route::post('/update/{id}', [TaxiManagementController::class, 'updateCoupon'])->name('update');
            Route::delete('/delete/{id}', [TaxiManagementController::class, 'deleteCoupon'])->name('delete');
            Route::get('/status/{id}/{status}', [TaxiManagementController::class, 'couponStatus'])->name('status');
        });
    });
});
