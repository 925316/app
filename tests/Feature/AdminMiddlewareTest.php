<?php

use App\Models\License;

it('blocks unauthenticated users from admin routes', function () {
    $response = $this->get(route('accounts.index'));
    $response->assertRedirect(route('login'));
});

it('blocks non-admin users from account management', function () {
    $user = createUserWithLicense(1);

    $this->actingAs($user)->get(route('accounts.index'))->assertForbidden();
    $this->actingAs($user)->get(route('accounts.create'))->assertForbidden();
    $this->actingAs($user)->get(route('sessions.index'))->assertForbidden();
    $this->actingAs($user)->get(route('logs.index'))->assertForbidden();
});

it('allows admin users to access account management', function () {
    $admin = createAdmin();

    $this->actingAs($admin)->get(route('accounts.index'))->assertSuccessful();
});

it('allows admin users to access session management', function () {
    $admin = createAdmin();

    $this->actingAs($admin)->get(route('sessions.index'))->assertSuccessful();
});

it('allows admin users to access log management', function () {
    $admin = createAdmin();

    $this->actingAs($admin)->get(route('logs.index'))->assertSuccessful();
});

it('blocks non-admin users from admin device operations', function () {
    $user = createUserWithLicense(1);

    $this->actingAs($user)->get(route('devices.export'))->assertForbidden();
    $this->actingAs($user)->post(route('devices.bulk-unbind-admin'), ['device_ids' => []])->assertForbidden();
    $this->actingAs($user)->post(route('devices.bulk-reset-hwid-admin'), ['device_ids' => []])->assertForbidden();
});

it('blocks non-admin users from admin license operations', function () {
    $user = createUserWithLicense(1);
    $license = License::factory()->create();

    $this->actingAs($user)->get(route('licenses.create'))->assertForbidden();
    $this->actingAs($user)->delete(route('licenses.destroy', $license))->assertForbidden();
});

it('blocks non-admin users from admin package operations', function () {
    $user = createUserWithLicense(1);

    $this->actingAs($user)->get(route('packages.upload'))->assertForbidden();
    $this->actingAs($user)->get(route('packages.manage'))->assertForbidden();
});
