<?php

use App\Models\Account;
use App\Models\AccountDevice;

beforeEach(function () {
    $this->account = Account::factory()->create();
    $this->device = AccountDevice::factory()->create([
        'account_id' => $this->account->id,
        'hwid_hash' => str_repeat('a', 64),
        'ip_address' => '192.168.1.1',
        'country_code' => 'US',
        'bound_at' => now(),
        'unbound_at' => null,
        'first_seen_at' => now()->subDays(10),
        'last_seen_at' => now(),
    ]);
});

it('can check if device is bound', function () {
    expect($this->device->isBound())->toBeTrue();

    $this->device->update(['unbound_at' => now()]);
    expect($this->device->fresh()->isBound())->toBeFalse();
});

it('returns false when bound_at is null', function () {
    $device = AccountDevice::factory()->create([
        'account_id' => $this->account->id,
        'bound_at' => null,
    ]);

    expect($device->isBound())->toBeFalse();
});

it('can check if account has bound device', function () {
    expect(AccountDevice::hasBoundDevice($this->account->id))->toBeTrue();

    $this->device->update(['unbound_at' => now()]);
    expect(AccountDevice::hasBoundDevice($this->account->id))->toBeFalse();
});

it('can get bound device for account', function () {
    $found = AccountDevice::getBoundDevice($this->account->id);

    expect($found->id)->toBe($this->device->id);
});

it('returns null when no bound device', function () {
    $this->device->update(['unbound_at' => now()]);

    $found = AccountDevice::getBoundDevice($this->account->id);

    expect($found)->toBeNull();
});

it('has bound scope', function () {
    $account = Account::factory()->create();

    AccountDevice::factory()->create([
        'account_id' => $account->id,
        'bound_at' => now(),
        'unbound_at' => null,
    ]);

    AccountDevice::factory()->create([
        'account_id' => $account->id,
        'bound_at' => now(),
        'unbound_at' => now(),
    ]);

    $boundDevices = AccountDevice::bound()->get();
    $boundIds = $boundDevices->pluck('id');

    expect($boundIds->contains($this->device->id))->toBeTrue();
    expect($boundIds->contains($account->devices()->whereNull('unbound_at')->first()->id))->toBeTrue();
    expect($boundIds->contains($account->devices()->whereNotNull('unbound_at')->first()->id))->toBeFalse();
});

it('has unbound scope', function () {
    $account = Account::factory()->create();

    AccountDevice::factory()->create([
        'account_id' => $account->id,
        'bound_at' => now(),
        'unbound_at' => now(),
    ]);

    AccountDevice::factory()->create([
        'account_id' => $account->id,
        'bound_at' => now(),
        'unbound_at' => null,
    ]);

    $unboundDevices = AccountDevice::unbound()->get();

    expect($unboundDevices)->toHaveCount(1);
});

it('has active scope', function () {
    // Use a fresh account to avoid interference from beforeEach
    $account = Account::factory()->create();
    $otherAccount = Account::factory()->create();

    AccountDevice::factory()->create([
        'account_id' => $account->id,
        'last_seen_at' => now()->subDays(5),
        'bound_at' => now(),
    ]);

    AccountDevice::factory()->create([
        'account_id' => $otherAccount->id,
        'last_seen_at' => now()->subDays(60),
        'bound_at' => now(),
    ]);

    $activeDevices = AccountDevice::active(30)
        ->where('account_id', $account->id)
        ->get();

    expect($activeDevices)->toHaveCount(1);
});

it('can get binding status attribute', function () {
    expect($this->device->binding_status)->toBe('bound');

    $this->device->update(['bound_at' => null]);
    expect($this->device->fresh()->binding_status)->toBe('never_bound');

    $this->device->update(['bound_at' => now(), 'unbound_at' => now()]);
    expect($this->device->fresh()->binding_status)->toBe('unbound');
});

it('can get device age in days', function () {
    expect($this->device->device_age_in_days)->toBeGreaterThanOrEqual(10);
});

it('can get last activity human attribute', function () {
    expect($this->device->last_activity_human)->toContain('ago');
});

it('can access account relationship', function () {
    expect($this->device->account->id)->toBe($this->account->id);
});
