<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\LicenseController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SessionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Admin-only routes — registered first so static paths take precedence over
// the dynamic resource routes registered in the general auth group below.
Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    // Accounts
    Route::resource('accounts', AccountController::class);
    Route::post('/accounts/{account}/suspend', [AccountController::class, 'suspend'])->name('accounts.suspend');
    Route::post('/accounts/{account}/unsuspend', [AccountController::class, 'unsuspend'])->name('accounts.unsuspend');
    Route::post('/accounts/{account}/reset-hwid', [AccountController::class, 'resetHwid'])->name('accounts.reset-hwid');
    Route::post('/accounts/{account}/verify-email', [AccountController::class, 'verifyEmail'])->name('accounts.verify-email');

    // Sessions
    Route::resource('sessions', SessionController::class)->only(['index', 'show', 'destroy']);

    // Logs
    Route::resource('logs', LogController::class)->only(['index', 'show']);
    Route::post('/logs/clear', [LogController::class, 'clear'])->name('logs.clear');

    // Admin device operations
    Route::get('/devices/export', [DeviceController::class, 'export'])->name('devices.export');
    Route::post('/devices/{device}/unbind-admin', [DeviceController::class, 'adminUnbind'])->name('devices.unbind-admin');
    Route::post('/devices/account/{account}/reset-hwid-admin', [DeviceController::class, 'adminResetHwid'])->name('devices.reset-hwid-admin');
    Route::post('/devices/bulk-unbind-admin', [DeviceController::class, 'bulkUnbind'])->name('devices.bulk-unbind-admin');
    Route::post('/devices/bulk-reset-hwid-admin', [DeviceController::class, 'bulkResetHwid'])->name('devices.bulk-reset-hwid-admin');

    // Admin license operations
    Route::resource('licenses', LicenseController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
    Route::post('/licenses/{license}/suspend', [LicenseController::class, 'suspend'])->name('licenses.suspend');
    Route::post('/licenses/{license}/reactivate', [LicenseController::class, 'reactivate'])->name('licenses.reactivate');
    Route::post('/licenses/{license}/revoke', [LicenseController::class, 'revoke'])->name('licenses.revoke');
    Route::post('/licenses/{license}/upgrade', [LicenseController::class, 'upgrade'])->name('licenses.upgrade');
    Route::post('/licenses/{license}/extend', [LicenseController::class, 'extend'])->name('licenses.extend');

    // Admin package operations — static paths registered before the dynamic
    // GET /packages/{release} show route in the auth group below.
    Route::get('/packages/upload', [PackageController::class, 'upload'])->name('packages.upload');
    Route::post('/packages/upload', [PackageController::class, 'store'])->name('packages.store');
    Route::get('/packages/manage', [PackageController::class, 'manage'])->name('packages.manage');
    Route::post('/packages/{release}/update-changelog', [PackageController::class, 'updateChangelog'])->name('packages.update-changelog');
    Route::delete('/packages/bulk-delete', [PackageController::class, 'bulkDelete'])->name('packages.bulk-delete');
    Route::resource('packages', PackageController::class)
        ->parameters(['packages' => 'release'])
        ->only(['destroy']);
});

// Authenticated routes (all logged-in users)
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard - unified route, controller handles permission-based content
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Licenses - user-accessible routes
    Route::resource('licenses', LicenseController::class)->only(['index', 'show']);
    Route::post('/licenses/activate-by-key', [LicenseController::class, 'activateByKey'])->name('licenses.activate-by-key');
    Route::post('/licenses/{license}/activate', [LicenseController::class, 'activate'])->name('licenses.activate');

    // Devices - unified routes, controller handles permission-based content
    Route::get('/devices', [DeviceController::class, 'index'])->name('devices.index');
    Route::get('/devices/manage', [DeviceController::class, 'manage'])->name('devices.manage');
    Route::post('/devices/bind', [DeviceController::class, 'bind'])->name('devices.bind');
    Route::post('/devices/unbind', [DeviceController::class, 'unbind'])->name('devices.unbind');
    Route::post('/devices/reset-hwid', [DeviceController::class, 'resetHwid'])->name('devices.reset-hwid');

    // Packages - user-accessible routes
    Route::get('/packages/download/{release}', [PackageController::class, 'download'])->name('packages.download');
    Route::resource('packages', PackageController::class)
        ->parameters(['packages' => 'release'])
        ->only(['index', 'show']);
});

require __DIR__.'/auth.php';
