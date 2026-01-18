<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\LicenseController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/admin-panel', [DashboardController::class, 'adminPanel'])->name('dashboard.admin-panel');
    Route::get('/dashboard/user-panel', [DashboardController::class, 'userPanel'])->name('dashboard.user-panel');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Licenses
    Route::resource('licenses', LicenseController::class);
    Route::post('/licenses/activate-by-key', [LicenseController::class, 'activateByKey'])->name('licenses.activate-by-key');
    Route::post('/licenses/{license}/activate', [LicenseController::class, 'activate'])->name('licenses.activate');
    Route::post('/licenses/{license}/suspend', [LicenseController::class, 'suspend'])->name('licenses.suspend');
    Route::post('/licenses/{license}/reactivate', [LicenseController::class, 'reactivate'])->name('licenses.reactivate');
    Route::post('/licenses/{license}/revoke', [LicenseController::class, 'revoke'])->name('licenses.revoke');
    Route::post('/licenses/{license}/upgrade', [LicenseController::class, 'upgrade'])->name('licenses.upgrade');
    Route::post('/licenses/{license}/extend', [LicenseController::class, 'extend'])->name('licenses.extend');

    // Devices
    Route::resource('devices', DeviceController::class)->only(['index', 'manage']);
    Route::get('/devices/manage', [DeviceController::class, 'manage'])->name('devices.manage');
    Route::post('/devices/bind', [DeviceController::class, 'bind'])->name('devices.bind');
    Route::post('/devices/unbind', [DeviceController::class, 'unbind'])->name('devices.unbind');
    Route::post('/devices/reset-hwid', [DeviceController::class, 'resetHwid'])->name('devices.reset-hwid');

    // Packages
    Route::resource('packages', PackageController::class);
    Route::get('/packages/download/{package}', [PackageController::class, 'download'])->name('packages.download');
    Route::get('/packages/upload', [PackageController::class, 'upload'])->name('packages.upload');
    Route::post('/packages/upload', [PackageController::class, 'store'])->name('packages.store');
    Route::get('/packages/versions', [PackageController::class, 'versions'])->name('packages.versions');
    Route::post('/packages/{package}/update-changelog', [PackageController::class, 'updateChangelog'])->name('packages.update-changelog');

    // Admin-only routes
    Route::middleware('admin')->group(function () {
        // Logs
        Route::resource('logs', LogController::class)->only(['index', 'show']);
        Route::post('/logs/clear', [LogController::class, 'clear'])->name('logs.clear');
    });
});

require __DIR__.'/auth.php';
