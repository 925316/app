<?php

use App\Models\Account;
use App\Models\AccountDevice;
use App\Models\ClientSession;

beforeEach(function () {
    $this->admin = createAdmin();
    $this->regularUser = Account::factory()->active()->create([
        'hwid_last_reset_at' => null,
    ]);
});

it('admin can access device management page', function () {
    AccountDevice::factory()->create([
        'account_id' => $this->regularUser->id,
        'hwid_hash' => str_repeat('a', 64),
    ]);

    $response = $this->actingAs($this->admin)
        ->get(route('devices.index'));

    $response->assertSuccessful();
    $response->assertViewIs('devices.admin-index');
    $response->assertSee('data-page="devices-admin-index"', false);
    $response->assertSee('badge badge-default table-inline-copy max-w-full', false);
    $response->assertDontSee('hover:border-cool-400', false);
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
        'unboundDevices',
        'neverBoundDevices',
    ]);
});

it('never bound device stats and filter include only devices that were never bound', function () {
    $onlineBoundDevice = AccountDevice::factory()->create([
        'account_id' => $this->regularUser->id,
        'bound_at' => now()->subDays(2),
        'unbound_at' => null,
        'last_seen_at' => now()->subDays(5),
    ]);

    $boundWithoutOnlineHeartbeat = AccountDevice::factory()->create([
        'account_id' => $this->regularUser->id,
        'bound_at' => now()->subDays(6),
        'unbound_at' => null,
        'last_seen_at' => now()->subDays(45),
    ]);

    $unboundButSessionedDevice = AccountDevice::factory()->create([
        'account_id' => $this->regularUser->id,
        'bound_at' => now()->subDays(3),
        'unbound_at' => now()->subDay(),
        'last_seen_at' => now()->subDays(3),
    ]);

    $neverBound = AccountDevice::factory()->create([
        'account_id' => $this->regularUser->id,
        'bound_at' => null,
        'unbound_at' => null,
    ]);

    $response = $this->actingAs($this->admin)
        ->get(route('devices.index', ['status' => 'never_bound']));

    $response->assertSuccessful();
    $response->assertViewHas('neverBoundDevices', fn (int $count) => $count === 1);

    $filtered = $response->viewData('devices');
    $ids = collect($filtered->items())->pluck('id');

    expect($ids)->toHaveCount(1);

    $activeDevice = AccountDevice::query()->find($ids->first());
    expect($activeDevice)->not->toBeNull();
    expect($ids->contains($neverBound->id))->toBeTrue();
    expect($ids->contains($onlineBoundDevice->id))->toBeFalse();
    expect($ids->contains($unboundButSessionedDevice->id))->toBeFalse();
    expect($ids->contains($boundWithoutOnlineHeartbeat->id))->toBeFalse();
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

it('admin device index uses a single view action per row', function () {
    $deviceOwner = Account::factory()->create();
    $device = AccountDevice::factory()->create([
        'account_id' => $deviceOwner->id,
        'bound_at' => now(),
        'unbound_at' => null,
    ]);

    $this->actingAs($this->admin)
        ->get(route('devices.index'))
        ->assertSuccessful()
        ->assertSee('aria-label="Device row actions"', false)
        ->assertSee(route('accounts.show', $deviceOwner).'#account-device-'.$device->id, false)
        ->assertDontSee('devices.unbind-admin', false)
        ->assertDontSee('devices.reset-hwid-admin', false)
        ->assertDontSee('>Unbind<', false)
        ->assertDontSee('>Reset HWID<', false);
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

it('admin unbind invalidates sessions for that device', function () {
    $device = AccountDevice::factory()->create([
        'account_id' => $this->regularUser->id,
        'hwid_hash' => str_repeat('e', 64),
        'bound_at' => now(),
        'unbound_at' => null,
    ]);

    $session = ClientSession::factory()->create([
        'account_id' => $this->regularUser->id,
        'device_id' => $device->id,
    ]);

    $this->actingAs($this->admin)
        ->post(route('devices.unbind-admin', $device))
        ->assertRedirect(route('devices.index'));

    expect(ClientSession::query()->whereKey($session->id)->exists())->toBeFalse();
});

it('admin unbind only invalidates sessions for targeted device', function () {
    $targetDevice = AccountDevice::factory()->create([
        'account_id' => $this->regularUser->id,
        'hwid_hash' => str_repeat('1', 64),
        'bound_at' => now(),
        'unbound_at' => null,
    ]);

    $otherDevice = AccountDevice::factory()->create([
        'account_id' => $this->regularUser->id,
        'hwid_hash' => str_repeat('2', 64),
        'bound_at' => now(),
        'unbound_at' => null,
    ]);

    $targetSession = ClientSession::factory()->create([
        'account_id' => $this->regularUser->id,
        'device_id' => $targetDevice->id,
    ]);

    $otherSession = ClientSession::factory()->create([
        'account_id' => $this->regularUser->id,
        'device_id' => $otherDevice->id,
    ]);

    $this->actingAs($this->admin)
        ->post(route('devices.unbind-admin', $targetDevice))
        ->assertRedirect(route('devices.index'));

    expect(ClientSession::query()->whereKey($targetSession->id)->exists())->toBeFalse();
    expect(ClientSession::query()->whereKey($otherSession->id)->exists())->toBeTrue();
});

it('admin unbind returns error when device is not currently bound', function () {
    $device = AccountDevice::factory()->create([
        'account_id' => $this->regularUser->id,
        'hwid_hash' => str_repeat('a', 64),
        'bound_at' => now()->subDay(),
        'unbound_at' => now()->subHour(),
    ]);

    $this->actingAs($this->admin)
        ->post(route('devices.unbind-admin', $device))
        ->assertSessionHasErrors('device');
});

it('admin can reset HWID for a user', function () {
    $resettableUser = Account::factory()->active()->create([
        'hwid_last_reset_at' => null,
    ]);

    expect($resettableUser->canResetHwid())->toBeTrue();

    $initialCount = $resettableUser->hwid_reset_count;

    $response = $this->actingAs($this->admin)
        ->post(route('devices.reset-hwid-admin', $resettableUser));

    $response->assertRedirect(route('devices.index'));
    $response->assertSessionHas('success');

    expect($resettableUser->fresh()->hwid_reset_count)->toBe($initialCount + 1);
});

it('admin reset hwid invalidates all account sessions', function () {
    $resettableUser = Account::factory()->active()->create([
        'hwid_last_reset_at' => null,
    ]);

    $device = AccountDevice::factory()->create([
        'account_id' => $resettableUser->id,
        'hwid_hash' => str_repeat('f', 64),
        'bound_at' => now(),
        'unbound_at' => null,
    ]);

    ClientSession::factory()->count(2)->create([
        'account_id' => $resettableUser->id,
        'device_id' => $device->id,
    ]);

    $this->actingAs($this->admin)
        ->post(route('devices.reset-hwid-admin', $resettableUser))
        ->assertRedirect(route('devices.index'));

    expect(ClientSession::query()->where('account_id', $resettableUser->id)->count())->toBe(0);
});

it('admin reset HWID returns cooldown error when account is in cooldown window', function () {
    $cooldownUser = Account::factory()->create([
        'hwid_last_reset_at' => now()->subHours(1),
    ]);

    $response = $this->actingAs($this->admin)
        ->post(route('devices.reset-hwid-admin', $cooldownUser));

    $response->assertSessionHasErrors('hwid_reset');
});

it('admin can perform bulk unbind', function () {
    $userA = Account::factory()->create();
    $userB = Account::factory()->create();

    $device1 = AccountDevice::factory()->create([
        'account_id' => $userA->id,
        'hwid_hash' => str_repeat('a', 64),
        'bound_at' => now(),
        'unbound_at' => null,
    ]);

    $device2 = AccountDevice::factory()->create([
        'account_id' => $userB->id,
        'hwid_hash' => str_repeat('b', 64),
        'bound_at' => now(),
        'unbound_at' => null,
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

it('bulk unbind returns bulk action error when selected devices are already unbound', function () {
    $device = AccountDevice::factory()->create([
        'account_id' => $this->regularUser->id,
        'hwid_hash' => str_repeat('d', 64),
        'bound_at' => now()->subDay(),
        'unbound_at' => now()->subHour(),
    ]);

    $this->actingAs($this->admin)
        ->post(route('devices.bulk-unbind-admin'), [
            'device_ids' => [$device->id],
        ])
        ->assertSessionHasErrors('bulk_action');
});

it('admin can perform bulk HWID reset', function () {
    $resettableUser = Account::factory()->active()->create([
        'hwid_last_reset_at' => null,
    ]);

    expect($resettableUser->canResetHwid())->toBeTrue();

    $device = AccountDevice::factory()->create([
        'account_id' => $resettableUser->id,
        'hwid_hash' => str_repeat('a', 64),
        'bound_at' => now(),
        'unbound_at' => null,
    ]);

    $initialCount = $resettableUser->hwid_reset_count;

    $response = $this->actingAs($this->admin)
        ->post(route('devices.bulk-reset-hwid-admin'), [
            'device_ids' => [$device->id],
        ]);

    $response->assertRedirect(route('devices.index'));
    $response->assertSessionHas('success');

    expect($resettableUser->fresh()->hwid_reset_count)->toBe($initialCount + 1);
});

it('bulk reset hwid deduplicates selected devices of same account', function () {
    $user = Account::factory()->active()->create([
        'hwid_last_reset_at' => null,
        'hwid_reset_count' => 0,
    ]);

    $deviceA = AccountDevice::factory()->create([
        'account_id' => $user->id,
        'bound_at' => now(),
        'unbound_at' => null,
    ]);

    $deviceB = AccountDevice::factory()->create([
        'account_id' => $user->id,
        'bound_at' => now()->subDay(),
        'unbound_at' => now()->subHour(),
    ]);

    $this->actingAs($this->admin)
        ->post(route('devices.bulk-reset-hwid-admin'), [
            'device_ids' => [$deviceA->id, $deviceB->id],
        ])
        ->assertRedirect(route('devices.index'))
        ->assertSessionHas('success');

    expect($user->fresh()->hwid_reset_count)->toBe(1);
});

it('bulk unbind mixed selection only unbinds bound devices', function () {
    $boundDevice = AccountDevice::factory()->create([
        'account_id' => $this->regularUser->id,
        'bound_at' => now(),
        'unbound_at' => null,
    ]);

    $alreadyUnbound = AccountDevice::factory()->create([
        'account_id' => $this->regularUser->id,
        'bound_at' => now()->subDay(),
        'unbound_at' => now()->subHour(),
    ]);

    $this->actingAs($this->admin)
        ->post(route('devices.bulk-unbind-admin'), [
            'device_ids' => [$boundDevice->id, $alreadyUnbound->id],
        ])
        ->assertRedirect(route('devices.index'))
        ->assertSessionHas('success');

    expect($boundDevice->fresh()->unbound_at)->not->toBeNull();
    expect($alreadyUnbound->fresh()->unbound_at)->not->toBeNull();
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

it('bulk reset validates at least one device id', function () {
    $this->actingAs($this->admin)
        ->post(route('devices.bulk-reset-hwid-admin'), [
            'device_ids' => [],
        ])
        ->assertSessionHasErrors('device_ids');
});

it('bulk reset validates each device id exists', function () {
    $this->actingAs($this->admin)
        ->post(route('devices.bulk-reset-hwid-admin'), [
            'device_ids' => [999999],
        ])
        ->assertSessionHasErrors('device_ids.0');
});

it('bulk reset returns bulk action error when all selected accounts are in cooldown', function () {
    $cooldownUser = Account::factory()->create([
        'hwid_last_reset_at' => now()->subHours(1),
    ]);

    $device = AccountDevice::factory()->create([
        'account_id' => $cooldownUser->id,
        'hwid_hash' => str_repeat('c', 64),
        'bound_at' => now(),
        'unbound_at' => null,
    ]);

    $response = $this->actingAs($this->admin)
        ->post(route('devices.bulk-reset-hwid-admin'), [
            'device_ids' => [$device->id],
        ]);

    $response->assertSessionHasErrors('bulk_action');
});
