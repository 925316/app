<?php

use App\Models\Account;
use App\Models\AccountDevice;
use App\Models\ClientSession;

beforeEach(function () {
    $this->userWithLicense = createUserWithLicense(1);
    $this->userWithoutLicense = Account::factory()->create();
});

// --- Index (user view) ---

it('user with license sees their own device list view', function () {
    $this->actingAs($this->userWithLicense)
        ->get(route('devices.index'))
        ->assertSuccessful()
        ->assertViewIs('devices.index');
});

it('user without license cannot access devices index', function () {
    $this->actingAs($this->userWithoutLicense)
        ->get(route('devices.index'))
        ->assertForbidden();
});

it('guest is redirected from user device routes', function () {
    $this->get(route('devices.index'))->assertRedirect(route('login'));
    $this->get(route('devices.manage'))->assertRedirect(route('login'));
    $this->post(route('devices.bind'))->assertRedirect(route('login'));
    $this->post(route('devices.unbind'))->assertRedirect(route('login'));
    $this->post(route('devices.reset-hwid'))->assertRedirect(route('login'));
});

it('devices index shows only current users devices', function () {
    AccountDevice::factory()->create([
        'account_id' => $this->userWithLicense->id,
        'hwid_hash' => str_repeat('a', 64),
        'bound_at' => now(),
    ]);

    $otherUser = Account::factory()->create();
    AccountDevice::factory()->create([
        'account_id' => $otherUser->id,
        'hwid_hash' => str_repeat('b', 64),
        'bound_at' => now(),
    ]);

    $response = $this->actingAs($this->userWithLicense)
        ->get(route('devices.index'));

    $response->assertSuccessful();
    $devices = $response->viewData('devices');
    foreach ($devices as $device) {
        expect($device->account_id)->toBe($this->userWithLicense->id);
    }
});

// --- Manage ---

it('user with license can access device manage page', function () {
    $this->actingAs($this->userWithLicense)
        ->get(route('devices.manage'))
        ->assertSuccessful()
        ->assertViewIs('devices.manage')
        ->assertSee('data-page="devices-manage"', false)
        ->assertSee('form-note text-xs', false)
        ->assertDontSee('text-gray-500 dark:text-gray-400', false)
        ->assertViewHasAll(['currentDevice', 'canResetHwid', 'hwidResetCount', 'hwidLastReset']);
});

it('user without license cannot access device manage page', function () {
    $this->actingAs($this->userWithoutLicense)
        ->get(route('devices.manage'))
        ->assertForbidden();
});

// --- Bind ---

it('user with license can bind a device', function () {
    $this->actingAs($this->userWithLicense)
        ->post(route('devices.bind'), [
            'hwid' => 'USER-HWID-A-12345',
            'ip_address' => '192.168.1.1',
            'country_code' => 'US',
        ])
        ->assertRedirect(route('devices.manage'))
        ->assertSessionHas('success');

    expect($this->userWithLicense->devices()->whereNotNull('bound_at')->whereNull('unbound_at')->exists())->toBeTrue();
});

it('user without license cannot bind a device', function () {
    $this->actingAs($this->userWithoutLicense)
        ->post(route('devices.bind'), [
            'hwid' => 'USER-HWID-A-12345',
            'ip_address' => '192.168.1.1',
        ])
        ->assertForbidden();
});

it('user cannot bind a second device when one is already bound', function () {
    AccountDevice::factory()->create([
        'account_id' => $this->userWithLicense->id,
        'hwid_hash' => str_repeat('a', 64),
        'bound_at' => now(),
        'unbound_at' => null,
    ]);

    $this->actingAs($this->userWithLicense)
        ->post(route('devices.bind'), [
            'hwid' => 'USER-HWID-B-67890',
            'ip_address' => '192.168.1.2',
        ])
        ->assertSessionHasErrors('hwid');
});

// --- Unbind ---

it('user with license can unbind their current device', function () {
    $device = AccountDevice::factory()->create([
        'account_id' => $this->userWithLicense->id,
        'hwid_hash' => str_repeat('a', 64),
        'bound_at' => now(),
        'unbound_at' => null,
    ]);

    $this->actingAs($this->userWithLicense)
        ->post(route('devices.unbind'))
        ->assertRedirect(route('devices.manage'))
        ->assertSessionHas('success');

    expect($device->fresh()->unbound_at)->not->toBeNull();
});

it('user unbind invalidates sessions for that device', function () {
    $device = AccountDevice::factory()->create([
        'account_id' => $this->userWithLicense->id,
        'hwid_hash' => str_repeat('a', 64),
        'bound_at' => now(),
        'unbound_at' => null,
    ]);

    $session = ClientSession::factory()->create([
        'account_id' => $this->userWithLicense->id,
        'device_id' => $device->id,
    ]);

    $this->actingAs($this->userWithLicense)
        ->post(route('devices.unbind'))
        ->assertRedirect(route('devices.manage'));

    expect(ClientSession::query()->whereKey($session->id)->exists())->toBeFalse();
});

it('unbind fails when no device is bound', function () {
    $this->actingAs($this->userWithLicense)
        ->post(route('devices.unbind'))
        ->assertSessionHasErrors('device');
});

it('user without license cannot unbind a device', function () {
    $this->actingAs($this->userWithoutLicense)
        ->post(route('devices.unbind'))
        ->assertForbidden();
});

// --- Reset HWID (self-service) ---

it('user with license can reset their own HWID', function () {
    $initialCount = $this->userWithLicense->hwid_reset_count;

    $this->actingAs($this->userWithLicense)
        ->post(route('devices.reset-hwid'))
        ->assertRedirect(route('devices.manage'))
        ->assertSessionHas('success');

    expect($this->userWithLicense->fresh()->hwid_reset_count)->toBe($initialCount + 1);
});

it('user hwid reset invalidates all account sessions', function () {
    $device = AccountDevice::factory()->create([
        'account_id' => $this->userWithLicense->id,
        'hwid_hash' => str_repeat('c', 64),
        'bound_at' => now(),
        'unbound_at' => null,
    ]);

    ClientSession::factory()->count(2)->create([
        'account_id' => $this->userWithLicense->id,
        'device_id' => $device->id,
    ]);

    $this->actingAs($this->userWithLicense)
        ->post(route('devices.reset-hwid'))
        ->assertRedirect(route('devices.manage'));

    expect(ClientSession::query()->where('account_id', $this->userWithLicense->id)->count())->toBe(0);
});

it('user without license cannot reset HWID', function () {
    $this->actingAs($this->userWithoutLicense)
        ->post(route('devices.reset-hwid'))
        ->assertForbidden();
});

it('user cannot reset HWID within 72 hour cooldown', function () {
    $this->userWithLicense->update(['hwid_last_reset_at' => now()->subHours(10)]);

    $this->actingAs($this->userWithLicense)
        ->post(route('devices.reset-hwid'))
        ->assertSessionHasErrors('hwid_reset');
});

it('bind validates ip_address format', function () {
    $this->actingAs($this->userWithLicense)
        ->post(route('devices.bind'), [
            'hwid' => 'USER-HWID-A-12345',
            'ip_address' => 'not-an-ip',
            'country_code' => 'US',
        ])
        ->assertSessionHasErrors('ip_address');
});

it('bind validates country_code format', function () {
    $this->actingAs($this->userWithLicense)
        ->post(route('devices.bind'), [
            'hwid' => 'USER-HWID-A-12345',
            'ip_address' => '192.168.1.1',
            'country_code' => 'U1',
        ])
        ->assertSessionHasErrors('country_code');
});

it('bind requires hwid field', function () {
    $this->actingAs($this->userWithLicense)
        ->post(route('devices.bind'), [
            'ip_address' => '192.168.1.1',
            'country_code' => 'US',
        ])
        ->assertSessionHasErrors('hwid');
});

it('bind validates hwid minimum length', function () {
    $this->actingAs($this->userWithLicense)
        ->post(route('devices.bind'), [
            'hwid' => 'SHORT',
            'ip_address' => '192.168.1.1',
            'country_code' => 'US',
        ])
        ->assertSessionHasErrors('hwid');
});

it('bind rejects same hwid when already bound to account', function () {
    $hwid = 'USER-HWID-A-12345';
    AccountDevice::factory()->create([
        'account_id' => $this->userWithLicense->id,
        'hwid_hash' => hash('sha256', $hwid),
        'bound_at' => now(),
        'unbound_at' => null,
    ]);

    $this->actingAs($this->userWithLicense)
        ->post(route('devices.bind'), [
            'hwid' => $hwid,
            'ip_address' => '192.168.1.2',
            'country_code' => 'US',
        ])
        ->assertSessionHasErrors('hwid');
});

it('bind trims hwid before duplicate check', function () {
    $hwid = 'USER-HWID-TRIM-12345';

    AccountDevice::factory()->create([
        'account_id' => $this->userWithLicense->id,
        'hwid_hash' => hash('sha256', $hwid),
        'bound_at' => now(),
        'unbound_at' => null,
    ]);

    $this->actingAs($this->userWithLicense)
        ->post(route('devices.bind'), [
            'hwid' => '  '.$hwid.'  ',
            'ip_address' => '192.168.1.9',
            'country_code' => 'us',
        ])
        ->assertSessionHasErrors('hwid');
});

it('bind normalizes lowercase country code before persisting', function () {
    $this->actingAs($this->userWithLicense)
        ->post(route('devices.bind'), [
            'hwid' => 'USER-HWID-COUNTRY-12345',
            'ip_address' => '192.168.1.10',
            'country_code' => 'us',
        ])
        ->assertRedirect(route('devices.manage'));

    $device = $this->userWithLicense->devices()->latest('id')->first();
    expect($device)->not->toBeNull();
    expect($device?->country_code)->toBe('US');
});

it('user unbind only deletes sessions for currently bound device', function () {
    $boundDevice = AccountDevice::factory()->create([
        'account_id' => $this->userWithLicense->id,
        'hwid_hash' => str_repeat('1', 64),
        'bound_at' => now(),
        'unbound_at' => null,
    ]);

    $oldDevice = AccountDevice::factory()->create([
        'account_id' => $this->userWithLicense->id,
        'hwid_hash' => str_repeat('2', 64),
        'bound_at' => now()->subDays(3),
        'unbound_at' => now()->subDay(),
    ]);

    $currentSession = ClientSession::factory()->create([
        'account_id' => $this->userWithLicense->id,
        'device_id' => $boundDevice->id,
    ]);

    $oldSession = ClientSession::factory()->create([
        'account_id' => $this->userWithLicense->id,
        'device_id' => $oldDevice->id,
    ]);

    $this->actingAs($this->userWithLicense)
        ->post(route('devices.unbind'))
        ->assertRedirect(route('devices.manage'));

    expect(ClientSession::query()->whereKey($currentSession->id)->exists())->toBeFalse();
    expect(ClientSession::query()->whereKey($oldSession->id)->exists())->toBeTrue();
});

it('user reset hwid does not delete sessions for other accounts', function () {
    $device = AccountDevice::factory()->create([
        'account_id' => $this->userWithLicense->id,
        'bound_at' => now(),
        'unbound_at' => null,
    ]);

    ClientSession::factory()->create([
        'account_id' => $this->userWithLicense->id,
        'device_id' => $device->id,
    ]);

    $otherAccount = Account::factory()->create();
    $otherDevice = AccountDevice::factory()->create([
        'account_id' => $otherAccount->id,
        'bound_at' => now(),
        'unbound_at' => null,
    ]);
    $otherSession = ClientSession::factory()->create([
        'account_id' => $otherAccount->id,
        'device_id' => $otherDevice->id,
    ]);

    $this->actingAs($this->userWithLicense)
        ->post(route('devices.reset-hwid'))
        ->assertRedirect(route('devices.manage'));

    expect(ClientSession::query()->whereKey($otherSession->id)->exists())->toBeTrue();
});
