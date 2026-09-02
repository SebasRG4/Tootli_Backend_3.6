<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Espacios Web Routes
|--------------------------------------------------------------------------
*/

use Modules\Espacios\Http\Controllers\Admin\EspaciosAdminController;

Route::group(['prefix' => 'admin/espacios', 'as' => 'admin.espacios.', 'middleware' => ['admin']], function () {
    Route::get('index', [EspaciosAdminController::class, 'index'])->name('index');
    Route::get('create', [EspaciosAdminController::class, 'create'])->name('create');
    Route::post('store', [EspaciosAdminController::class, 'store'])->name('store');
    Route::get('edit/{id}', [EspaciosAdminController::class, 'edit'])->name('edit');
    Route::put('update/{id}', [EspaciosAdminController::class, 'update'])->name('update');
    Route::post('status', [EspaciosAdminController::class, 'status'])->name('status');
    Route::delete('delete/{id}', [EspaciosAdminController::class, 'destroy'])->name('delete');
});
