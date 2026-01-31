<?php

use App\Models\Account;
use App\Models\AccountDevice;
use App\Models\License;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('admin can access device management page', function () {
    $admin = Account::factory()->create([
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password' => Hash::make('password'),
    ]);

    // Give admin privileges
    License::factory()->active()->privilege(7)->create([
        'used_by' => $admin->id,
        'expires_at' => now()->addYear(),
    ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('devices.index'));

    $response->assertSuccessful();
    $response->assertViewIs('devices.admin-index');
});

test('non-admin cannot access device management page', function () {
    $user = Account::factory()->create([
        'username' => 'user',
        'email' => 'user@example.com',
        'password' => Hash::make('password'),
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('devices.index'));

    $response->assertForbidden();
});

test('admin can see device statistics', function () {
    $admin = Account::factory()->create([
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password' => Hash::make('password'),
    ]);

    // Give admin privileges
    License::factory()->active()->privilege(7)->create([
        'used_by' => $admin->id,
        'expires_at' => now()->addYear(),
    ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('devices.index'));

    $response->assertSuccessful();
    $response->assertViewHasAll([
        'totalDevices',
        'boundDevices',
        'activeDevices',
        'unboundDevices',
    ]);
});

test('admin can filter devices by status', function () {
    $admin = Account::factory()->create([
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password' => Hash::make('password'),
    ]);

    // Give admin privileges
    License::factory()->active()->privilege(7)->create([
        'used_by' => $admin->id,
        'expires_at' => now()->addYear(),
    ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('devices.index', ['status' => 'bound']));

    $response->assertSuccessful();
    $response->assertViewHas('devices');
});

test('admin can filter devices by date range', function () {
    $admin = Account::factory()->create([
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password' => Hash::make('password'),
    ]);

    // Give admin privileges
    License::factory()->active()->privilege(7)->create([
        'used_by' => $admin->id,
        'expires_at' => now()->addYear(),
    ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('devices.index', ['date_range' => '7d']));

    $response->assertSuccessful();
    $response->assertViewHas('devices');
});

test('admin can search devices', function () {
    $admin = Account::factory()->create([
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password' => Hash::make('password'),
    ]);

    // Give admin privileges
    License::factory()->active()->privilege(7)->create([
        'used_by' => $admin->id,
        'expires_at' => now()->addYear(),
    ]);

    $user = Account::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
    ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('devices.index', ['search' => 'testuser']));

    $response->assertSuccessful();
    $response->assertViewHas('devices');
});

test('admin can unbind a device', function () {
    $admin = Account::factory()->create([
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password' => Hash::make('password'),
    ]);

    // Give admin privileges
    License::factory()->active()->privilege(7)->create([
        'used_by' => $admin->id,
        'expires_at' => now()->addYear(),
    ]);

    $user = Account::factory()->create([
        'username' => 'user',
        'email' => 'user@example.com',
    ]);

    $device = AccountDevice::factory()->create([
        'account_id' => $user->id,
        'hwid_hash' => str_repeat('a', 64),
        'ip_address' => '192.168.1.1',
        'country_code' => 'US',
        'bound_at' => now(),
        'first_seen_at' => now(),
        'last_seen_at' => now(),
        'unbound_at' => null,
    ]);

    // Ensure the device is actually bound
    $this->assertTrue($device->fresh()->isBound(), 'Device should be bound before unbinding');

    $response = $this
        ->actingAs($admin)
        ->post(route('devices.unbind-admin', $device));

    $response->assertRedirect(route('devices.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('account_devices', [
        'id' => $device->id,
        'unbound_at' => $device->fresh()->unbound_at,
    ]);
});

test('admin can reset HWID for a user', function () {
    $admin = Account::factory()->create([
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password' => Hash::make('password'),
    ]);

    // Give admin privileges
    License::factory()->active()->privilege(7)->create([
        'used_by' => $admin->id,
        'expires_at' => now()->addYear(),
    ]);

    $user = Account::factory()->create([
        'username' => 'user',
        'email' => 'user@example.com',
    ]);

    $response = $this
        ->actingAs($admin)
        ->post(route('devices.reset-hwid-admin', $user));

    $response->assertRedirect(route('devices.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('accounts', [
        'id' => $user->id,
        'hwid_reset_count' => $user->fresh()->hwid_reset_count,
    ]);
});

test('admin can perform bulk unbind', function () {
    $admin = Account::factory()->create([
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password' => Hash::make('password'),
    ]);

    // Give admin privileges
    License::factory()->active()->privilege(7)->create([
        'used_by' => $admin->id,
        'expires_at' => now()->addYear(),
    ]);

    $user = Account::factory()->create([
        'username' => 'user',
        'email' => 'user@example.com',
    ]);

    $device1 = AccountDevice::factory()->create([
        'account_id' => $user->id,
        'hwid_hash' => str_repeat('a', 64),
        'bound_at' => now(),
    ]);

    $device2 = AccountDevice::factory()->create([
        'account_id' => $user->id,
        'hwid_hash' => str_repeat('b', 64),
        'bound_at' => now(),
    ]);

    $response = $this
        ->actingAs($admin)
        ->post(route('devices.bulk-unbind-admin'), [
            'device_ids' => [$device1->id, $device2->id],
        ]);

    $response->assertRedirect(route('devices.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('account_devices', [
        'id' => $device1->id,
        'unbound_at' => $device1->fresh()->unbound_at,
    ]);

    $this->assertDatabaseHas('account_devices', [
        'id' => $device2->id,
        'unbound_at' => $device2->fresh()->unbound_at,
    ]);
});

test('admin can perform bulk HWID reset', function () {
    $admin = Account::factory()->create([
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password' => Hash::make('password'),
    ]);

    // Give admin privileges
    License::factory()->active()->privilege(7)->create([
        'used_by' => $admin->id,
        'expires_at' => now()->addYear(),
    ]);

    $user = Account::factory()->create([
        'username' => 'user',
        'email' => 'user@example.com',
    ]);

    $device = AccountDevice::factory()->create([
        'account_id' => $user->id,
        'hwid_hash' => str_repeat('a', 64),
        'bound_at' => now(),
    ]);

    $response = $this
        ->actingAs($admin)
        ->post(route('devices.bulk-reset-hwid-admin'), [
            'device_ids' => [$device->id],
        ]);

    $response->assertRedirect(route('devices.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('accounts', [
        'id' => $user->id,
        'hwid_reset_count' => $user->fresh()->hwid_reset_count,
    ]);
});

test('admin can export device data', function () {
    $admin = Account::factory()->create([
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password' => Hash::make('password'),
    ]);

    // Give admin privileges
    License::factory()->active()->privilege(7)->create([
        'used_by' => $admin->id,
        'expires_at' => now()->addYear(),
    ]);

    $user = Account::factory()->create([
        'username' => 'user',
        'email' => 'user@example.com',
    ]);

    $device = AccountDevice::factory()->create([
        'account_id' => $user->id,
        'hwid_hash' => str_repeat('a', 64),
        'ip_address' => '192.168.1.1',
        'country_code' => 'US',
        'bound_at' => now(),
        'first_seen_at' => now(),
        'last_seen_at' => now(),
    ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('devices.export', ['status' => 'bound']));

    $response->assertSuccessful();
    $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
});

test('bulk actions require at least one device', function () {
    $admin = Account::factory()->create([
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password' => Hash::make('password'),
    ]);

    // Give admin privileges
    License::factory()->active()->privilege(7)->create([
        'used_by' => $admin->id,
        'expires_at' => now()->addYear(),
    ]);

    $response = $this
        ->actingAs($admin)
        ->post(route('devices.bulk-unbind-admin'), [
            'device_ids' => [],
        ]);

    $response->assertSessionHasErrors('device_ids');
});

test('bulk actions validate device IDs', function () {
    $admin = Account::factory()->create([
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password' => Hash::make('password'),
    ]);

    // Give admin privileges
    License::factory()->active()->privilege(7)->create([
        'used_by' => $admin->id,
        'expires_at' => now()->addYear(),
    ]);

    $response = $this
        ->actingAs($admin)
        ->post(route('devices.bulk-unbind-admin'), [
            'device_ids' => [999999],
        ]);

    $response->assertSessionHasErrors('device_ids.0');
});
