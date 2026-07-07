<?php

use App\Models\Account;
use App\Models\License;

it('blocks unauthenticated users from admin routes', function () {
    $response = $this->get(route('accounts.index'));
    $response->assertRedirect(route('login'));

    $session = \App\Models\ClientSession::factory()->create();
    $account = Account::factory()->create();

    $this->delete(route('sessions.destroy', $session))
        ->assertRedirect(route('login'));

    $this->get(route('sessions.show', $session))
        ->assertRedirect(route('login'));

    $this->post(route('logs.clear'), ['days' => 30])
        ->assertRedirect(route('login'));

    $signingKey = \App\Models\ApiSigningKey::factory()->create();

    $this->get(route('api-signing-keys.index'))
        ->assertRedirect(route('login'));

    $this->post(route('api-signing-keys.rotate'), ['confirm_rotation' => 1])
        ->assertRedirect(route('login'));

    $this->post(route('api-signing-keys.activate', $signingKey))
        ->assertRedirect(route('login'));

    $log = \App\Models\EventLog::factory()->create();

    $this->get(route('logs.index'))
        ->assertRedirect(route('login'));

    $this->get(route('logs.show', $log))
        ->assertRedirect(route('login'));

    $release = \App\Models\PackageRelease::factory()->create();

    $this->post(route('packages.store'), [
        'version' => '9.9.9',
        'release_channel' => 'stable',
        'download_url' => 'https://example.com/download/package-9.9.9.zip',
    ])->assertRedirect(route('login'));

    $this->post(route('packages.update-changelog', $release), [
        'changelog' => 'unauth',
    ])->assertRedirect(route('login'));

    $this->delete(route('packages.bulk-delete'), [
        'ids' => [$release->id],
    ])->assertRedirect(route('login'));

    $this->delete(route('packages.destroy', $release))
        ->assertRedirect(route('login'));

    $this->get(route('sessions.index'))
        ->assertRedirect(route('login'));

    $this->get(route('accounts.show', $account))
        ->assertRedirect(route('login'));
});

it('blocks non-admin users from account management', function () {
    $user = createUserWithLicense(1);
    $user->forceFill(['email_verified_at' => now()])->save();
    $session = \App\Models\ClientSession::factory()->create([
        'account_id' => $user->id,
    ]);
    $log = \App\Models\EventLog::factory()->create();
    $account = Account::factory()->create();
    $signingKey = \App\Models\ApiSigningKey::factory()->create();

    $this->actingAs($user)->get(route('accounts.index'))->assertForbidden();
    $this->actingAs($user)->get(route('accounts.create'))->assertForbidden();
    $this->actingAs($user)->post(route('accounts.store'), [
        'username' => 'blocked_user',
        'email' => 'blocked@example.com',
        'password' => 'Password1',
        'password_confirmation' => 'Password1',
    ])->assertForbidden();
    $this->actingAs($user)->get(route('accounts.show', $account))->assertForbidden();
    $this->actingAs($user)->get(route('accounts.edit', $account))->assertForbidden();
    $this->actingAs($user)->patch(route('accounts.update', $account), [
        'username' => 'still_blocked',
        'email' => 'still_blocked@example.com',
    ])->assertForbidden();
    $this->actingAs($user)->delete(route('accounts.destroy', $account))->assertForbidden();
    $this->actingAs($user)->post(route('accounts.suspend', $account))->assertForbidden();
    $this->actingAs($user)->post(route('accounts.unsuspend', $account))->assertForbidden();
    $this->actingAs($user)->post(route('accounts.reset-hwid', $account))->assertForbidden();
    $this->actingAs($user)->post(route('accounts.verify-email', $account))->assertForbidden();
    $this->actingAs($user)->get(route('sessions.index'))->assertForbidden();
    $this->actingAs($user)->get(route('sessions.show', $session))->assertForbidden();
    $this->actingAs($user)->delete(route('sessions.destroy', $session))->assertForbidden();
    $this->actingAs($user)->get(route('logs.index'))->assertForbidden();
    $this->actingAs($user)->post(route('logs.clear'), ['days' => 30])->assertForbidden();
    $this->actingAs($user)->get(route('logs.show', $log))->assertForbidden();
    $this->actingAs($user)->get(route('api-signing-keys.index'))->assertForbidden();
    $this->actingAs($user)->post(route('api-signing-keys.rotate'), ['confirm_rotation' => 1])->assertForbidden();
    $this->actingAs($user)->post(route('api-signing-keys.activate', $signingKey))->assertForbidden();
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

it('allows admin users to access signing key management', function () {
    $admin = createAdmin();

    $this->actingAs($admin)->get(route('api-signing-keys.index'))->assertSuccessful();
});

it('blocks non-admin users from admin device operations', function () {
    $user = createUserWithLicense(1);
    $user->forceFill(['email_verified_at' => now()])->save();
    $targetUser = Account::factory()->create();
    $device = \App\Models\AccountDevice::factory()->create([
        'account_id' => $targetUser->id,
    ]);

    $this->actingAs($user)->get(route('devices.export'))->assertForbidden();
    $this->actingAs($user)->post(route('devices.unbind-admin', $device))->assertForbidden();
    $this->actingAs($user)->post(route('devices.reset-hwid-admin', $targetUser))->assertForbidden();
    $this->actingAs($user)->post(route('devices.bulk-unbind-admin'), ['device_ids' => []])->assertForbidden();
    $this->actingAs($user)->post(route('devices.bulk-reset-hwid-admin'), ['device_ids' => []])->assertForbidden();
});

it('blocks non-admin users from admin license operations', function () {
    $user = createUserWithLicense(1);
    $user->forceFill(['email_verified_at' => now()])->save();
    $license = License::factory()->create();

    $this->actingAs($user)->get(route('licenses.create'))->assertForbidden();
    $this->actingAs($user)->post(route('licenses.store'), [
        'privilege' => 1,
        'status' => 0,
        'expires_at' => now()->addYear()->format('Y-m-d'),
    ])->assertForbidden();
    $this->actingAs($user)->get(route('licenses.edit', $license))->assertForbidden();
    $this->actingAs($user)->patch(route('licenses.update', $license), [
        'key' => $license->key,
        'privilege' => 1,
        'status' => 0,
        'expires_at' => now()->addYear()->format('Y-m-d'),
    ])->assertForbidden();
    $this->actingAs($user)->post(route('licenses.suspend', $license))->assertForbidden();
    $this->actingAs($user)->post(route('licenses.reactivate', $license))->assertForbidden();
    $this->actingAs($user)->post(route('licenses.revoke', $license))->assertForbidden();
    $this->actingAs($user)->post(route('licenses.upgrade', $license), ['new_privilege' => 3])->assertForbidden();
    $this->actingAs($user)->post(route('licenses.extend', $license), ['days' => 30])->assertForbidden();
    $this->actingAs($user)->delete(route('licenses.destroy', $license))->assertForbidden();
});

it('blocks non-admin users from admin package operations', function () {
    $user = createUserWithLicense(1);
    $user->forceFill(['email_verified_at' => now()])->save();

    $this->actingAs($user)->get(route('packages.upload'))->assertForbidden();
    $this->actingAs($user)->get(route('packages.manage'))->assertForbidden();
});

it('unverified admin is redirected to verification notice from admin routes', function () {
    $admin = createAdmin();
    $admin->forceFill(['email_verified_at' => null])->save();

    $this->actingAs($admin)
        ->get(route('accounts.index'))
        ->assertRedirect(route('verification.notice'));
});

it('forbids users with expired staff license from admin account routes', function () {
    $user = createUserWithLicense(7);
    $user->forceFill(['email_verified_at' => now()])->save();

    $staffLicense = $user->licenses()->first();
    expect($staffLicense)->not->toBeNull();

    $staffLicense?->update([
        'expires_at' => now()->subDay(),
    ]);

    $this->actingAs($user)
        ->get(route('accounts.index'))
        ->assertForbidden();
});
