<?php

use App\Models\Account;
use App\Models\AccountDevice;

beforeEach(function () {
    $this->admin = createAdmin();
    $this->regularUser = Account::factory()->create();
});

it('admin can access device management page', function () {
    $response = $this->actingAs($this->admin)
        ->get(route('devices.index'));

    $response->assertSuccessful();
    $response->assertViewIs('devices.admin-index');
});

it('non-admin cannot access device management page', function () {
    $response = $this->actingAs($this->regularUser)
        ->get(route('devices.index'));

    $response->assertForbidden();
});

it('admin can see device statistics', function () {
    $response = $this->actingAs($this->admin)
        ->get(route('devices.index'));

    $response->assertSuccessful();
    $response->assertViewHasAll([
        'totalDevices',
        'boundDevices',
        'activeDevices',
        'unboundDevices',
    ]);
});

it('admin can filter devices by status', function () {
    $response = $this->actingAs($this->admin)
        ->get(route('devices.index', ['status' => 'bound']));

    $response->assertSuccessful();
    $response->assertViewHas('devices');
});

it('admin can filter devices by date range', function () {
    $response = $this->actingAs($this->admin)
        ->get(route('devices.index', ['date_range' => '7d']));

    $response->assertSuccessful();
    $response->assertViewHas('devices');
});

it('admin can search devices', function () {
    $user = Account::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
    ]);

    $response = $this->actingAs($this->admin)
        ->get(route('devices.index', ['search' => 'testuser']));

    $response->assertSuccessful();
    $response->assertViewHas('devices');
});

it('admin can unbind a device', function () {
    $device = AccountDevice::factory()->create([
        'account_id' => $this->regularUser->id,
        'hwid_hash' => str_repeat('a', 64),
        'ip_address' => '192.168.1.1',
        'country_code' => 'US',
        'bound_at' => now(),
        'first_seen_at' => now(),
        'last_seen_at' => now(),
        'unbound_at' => null,
    ]);

    expect($device->isBound())->toBeTrue('Device should be bound before unbinding');

    $response = $this->actingAs($this->admin)
        ->post(route('devices.unbind-admin', $device));

    $response->assertRedirect(route('devices.index'));
    $response->assertSessionHas('success');

    expect($device->fresh()->unbound_at)->not->toBeNull();
});

it('admin can reset HWID for a user', function () {
    $initialCount = $this->regularUser->hwid_reset_count;

    $response = $this->actingAs($this->admin)
        ->post(route('devices.reset-hwid-admin', $this->regularUser));

    $response->assertRedirect(route('devices.index'));
    $response->assertSessionHas('success');

    expect($this->regularUser->fresh()->hwid_reset_count)->toBe($initialCount + 1);
});

it('admin can perform bulk unbind', function () {
    $device1 = AccountDevice::factory()->create([
        'account_id' => $this->regularUser->id,
        'hwid_hash' => str_repeat('a', 64),
        'bound_at' => now(),
    ]);

    $device2 = AccountDevice::factory()->create([
        'account_id' => $this->regularUser->id,
        'hwid_hash' => str_repeat('b', 64),
        'bound_at' => now(),
    ]);

    $response = $this->actingAs($this->admin)
        ->post(route('devices.bulk-unbind-admin'), [
            'device_ids' => [$device1->id, $device2->id],
        ]);

    $response->assertRedirect(route('devices.index'));
    $response->assertSessionHas('success');

    expect($device1->fresh()->unbound_at)->not->toBeNull();
    expect($device2->fresh()->unbound_at)->not->toBeNull();
});

it('admin can perform bulk HWID reset', function () {
    $device = AccountDevice::factory()->create([
        'account_id' => $this->regularUser->id,
        'hwid_hash' => str_repeat('a', 64),
        'bound_at' => now(),
    ]);

    $initialCount = $this->regularUser->hwid_reset_count;

    $response = $this->actingAs($this->admin)
        ->post(route('devices.bulk-reset-hwid-admin'), [
            'device_ids' => [$device->id],
        ]);

    $response->assertRedirect(route('devices.index'));
    $response->assertSessionHas('success');

    expect($this->regularUser->fresh()->hwid_reset_count)->toBe($initialCount + 1);
});

it('admin can export device data', function () {
    AccountDevice::factory()->create([
        'account_id' => $this->regularUser->id,
        'hwid_hash' => str_repeat('a', 64),
        'ip_address' => '192.168.1.1',
        'country_code' => 'US',
        'bound_at' => now(),
        'first_seen_at' => now(),
        'last_seen_at' => now(),
    ]);

    $response = $this->actingAs($this->admin)
        ->get(route('devices.export', ['status' => 'bound']));

    $response->assertSuccessful();
    $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
});

it('bulk actions require at least one device', function () {
    $response = $this->actingAs($this->admin)
        ->post(route('devices.bulk-unbind-admin'), [
            'device_ids' => [],
        ]);

    $response->assertSessionHasErrors('device_ids');
});

it('bulk actions validate device IDs', function () {
    $response = $this->actingAs($this->admin)
        ->post(route('devices.bulk-unbind-admin'), [
            'device_ids' => [999999],
        ]);

    $response->assertSessionHasErrors('device_ids.0');
});
