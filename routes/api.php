<?php

use App\Http\Controllers\ClientLicenseController;
use Illuminate\Support\Facades\Route;

Route::prefix('license')->group(function () {
    Route::post('/check', [ClientLicenseController::class, 'check'])
        ->name('api.license.check');
});
