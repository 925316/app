<?php

use App\Models\Account;
use App\Models\AccountDevice;
use App\Models\UsageStatistic;

it('unauthenticated user is redirected to login from dashboard', function () {
    $this->get(route('dashboard'))
        ->assertRedirect(route('login'));
});

it('admin can access dashboard', function () {
    $admin = createAdmin();

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertSuccessful();
});

it('user with license can access dashboard', function () {
    $user = createUserWithLicense(1);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful();
});

it('user without license can access dashboard', function () {
    $user = Account::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful();
});

it('admin sees the admin panel view', function () {
    $admin = createAdmin();

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertViewIs('dashboard.admin-panel')
        ->assertViewHasAll(['stats', 'recentActivity', 'databaseStatus']);
});

it('regular user sees the user panel view', function () {
    $user = createUserWithLicense(1);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertViewIs('dashboard.user-panel')
        ->assertViewHasAll(['userStats', 'activeLicense', 'boundDevices', 'usageTimeFormatted']);
});

it('expired admin level license user sees user panel', function () {
    $user = createUserWithLicense(7);
    $user->forceFill(['email_verified_at' => now()])->save();

    $staffLicense = $user->licenses()->first();
    expect($staffLicense)->not->toBeNull();
    $staffLicense?->update(['expires_at' => now()->subDay()]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertViewIs('dashboard.user-panel');
});

it('user dashboard counts only currently bound devices', function () {
    $user = createUserWithLicense(1);
    $user->forceFill(['email_verified_at' => now()])->save();

    AccountDevice::factory()->create([
        'account_id' => $user->id,
        'bound_at' => now(),
        'unbound_at' => null,
    ]);

    AccountDevice::factory()->create([
        'account_id' => $user->id,
        'bound_at' => now()->subDays(3),
        'unbound_at' => now()->subDay(),
    ]);

    $response = $this->actingAs($user)
        ->get(route('dashboard'));

    $response->assertSuccessful()->assertViewIs('dashboard.user-panel');
    expect($response->viewData('boundDevices'))->toBe(1);
});

it('user dashboard falls back to 0h when usage statistics are empty', function () {
    $user = createUserWithLicense(1);
    $user->forceFill(['email_verified_at' => now()])->save();

    UsageStatistic::query()->delete();

    $response = $this->actingAs($user)
        ->get(route('dashboard'));

    $response->assertSuccessful()->assertViewIs('dashboard.user-panel');
    expect($response->viewData('usageTimeFormatted'))->toBe('0h');
});
