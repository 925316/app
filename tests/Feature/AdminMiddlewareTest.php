<?php

use App\Enums\LicenseStatus;
use App\Models\Account;
use App\Models\License;
use App\Models\PackageRelease;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Helper to create an admin account with a staff license
function createAdminAccount(): Account
{
    $admin = Account::factory()->create();
    License::factory()->create([
        'used_by' => $admin->id,
        'privilege' => 7,
        'status' => LicenseStatus::ACTIVE,
        'expires_at' => now()->addYear(),
    ]);

    return $admin;
}

// Helper to create a regular (non-admin) account
function createRegularAccount(): Account
{
    return Account::factory()->create();
}

it('blocks unauthenticated users from admin routes', function () {
    $response = $this->get(route('accounts.index'));
    $response->assertRedirect(route('login'));
});

it('blocks non-admin users from account management', function () {
    $user = createRegularAccount();

    $this->actingAs($user)->get(route('accounts.index'))->assertForbidden();
    $this->actingAs($user)->get(route('accounts.create'))->assertForbidden();
    $this->actingAs($user)->get(route('sessions.index'))->assertForbidden();
    $this->actingAs($user)->get(route('logs.index'))->assertForbidden();
});

it('allows admin users to access account management', function () {
    $admin = createAdminAccount();

    $this->actingAs($admin)->get(route('accounts.index'))->assertSuccessful();
});

it('allows admin users to access session management', function () {
    $admin = createAdminAccount();

    $this->actingAs($admin)->get(route('sessions.index'))->assertSuccessful();
});

it('allows admin users to access log management', function () {
    $admin = createAdminAccount();

    $this->actingAs($admin)->get(route('logs.index'))->assertSuccessful();
});

it('blocks non-admin users from admin device operations', function () {
    $user = createRegularAccount();
    License::factory()->create([
        'used_by' => $user->id,
        'privilege' => 1,
        'status' => LicenseStatus::ACTIVE,
        'expires_at' => now()->addYear(),
    ]);

    $this->actingAs($user)->get(route('devices.export'))->assertForbidden();
    $this->actingAs($user)->post(route('devices.bulk-unbind-admin'), ['device_ids' => []])->assertForbidden();
    $this->actingAs($user)->post(route('devices.bulk-reset-hwid-admin'), ['device_ids' => []])->assertForbidden();
});

it('blocks non-admin users from admin license operations', function () {
    $user = createRegularAccount();
    $license = License::factory()->create();

    $this->actingAs($user)->get(route('licenses.create'))->assertForbidden();
    $this->actingAs($user)->delete(route('licenses.destroy', $license))->assertForbidden();
});

it('blocks non-admin users from admin package operations', function () {
    $user = createRegularAccount();
    License::factory()->create([
        'used_by' => $user->id,
        'privilege' => 1,
        'status' => LicenseStatus::ACTIVE,
        'expires_at' => now()->addYear(),
    ]);

    $this->actingAs($user)->get(route('packages.upload'))->assertForbidden();
    $this->actingAs($user)->get(route('packages.manage'))->assertForbidden();
});
