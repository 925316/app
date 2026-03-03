<?php

use App\Http\Controllers\ClientLicenseController;
use Illuminate\Support\Facades\Route;

Route::prefix('license')->group(function () {
    Route::post('/check', [ClientLicenseController::class, 'check'])
        ->name('api.license.check');

    Route::post('/activate', [ClientLicenseController::class, 'activate'])
        ->name('api.license.activate');
});
