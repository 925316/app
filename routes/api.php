<?php

use App\Http\Controllers\ClientLicenseController;
use App\Http\Controllers\ClientPackageController;
use Illuminate\Support\Facades\Route;

Route::prefix('account')->group(function () {
    Route::post('/login', [ClientLicenseController::class, 'login'])
        ->name('api.account.login');
});

Route::prefix('license')->group(function () {
    Route::post('/check', [ClientLicenseController::class, 'check'])
        ->name('api.license.check');

    Route::post('/activate', [ClientLicenseController::class, 'activate'])
        ->name('api.license.activate');

    Route::post('/unbind', [ClientLicenseController::class, 'unbind'])
        ->name('api.license.unbind');
});

Route::prefix('update')->group(function () {
    Route::get('/check', [ClientPackageController::class, 'check'])
        ->name('api.update.check');
});
