<?php

use Illuminate\Support\Facades\Route;
use Modules\Sabores\App\Http\Controllers\Admin\SaboresController;

Route::get('/', [SaboresController::class, 'dashboard'])->name('dashboard');
Route::get('/reservations', [SaboresController::class, 'reservations'])->name('reservations');
Route::get('/reservations/{id}', [SaboresController::class, 'reservationDetails'])->name('reservations.details');
Route::post('/reservations/{id}/status', [SaboresController::class, 'updateReservationStatus'])->name('reservations.status');
Route::get('/restaurants', [SaboresController::class, 'restaurants'])->name('restaurants');
Route::get('/restaurants/{id}/edit', [SaboresController::class, 'editRestaurant'])->name('restaurants.edit');
Route::post('/restaurants/{id}', [SaboresController::class, 'updateRestaurant'])->name('restaurants.update');
Route::get('/coupons', [SaboresController::class, 'coupons'])->name('coupons');
Route::get('/analytics', [SaboresController::class, 'analytics'])->name('analytics');
