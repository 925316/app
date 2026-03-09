<?php

use App\Enums\LicensePrivilege;
use App\Enums\LicenseStatus;
use App\Models\Account;
use App\Models\AccountDevice;
use App\Models\License;

beforeEach(function () {
    $this->account = Account::factory()->active()->create([
        'hwid_last_reset_at' => null,
    ]);
});

it('can check if account is suspended', function () {
    expect($this->account->isSuspended())->toBeFalse();

    $this->account->update(['is_suspended' => true]);
    expect($this->account->fresh()->isSuspended())->toBeTrue();
});

it('can check temporary suspension', function () {
    $this->account->update([
        'is_suspended' => false,
        'suspended_until' => now()->addDay(),
    ]);

    expect($this->account->fresh()->isSuspended())->toBeTrue();
});

it('returns false for expired temporary suspension', function () {
    $this->account->update([
        'is_suspended' => false,
        'suspended_until' => now()->subDay(),
    ]);

    expect($this->account->fresh()->isSuspended())->toBeFalse();
});

it('can suspend account', function () {
    $result = $this->account->suspend('Test reason', now()->addDays(7));

    expect($result)->toBeTrue();
    expect($this->account->fresh()->is_suspended)->toBeTrue();
    expect($this->account->suspension_reason)->toBe('Test reason');
    expect($this->account->suspended_until)->not->toBeNull();
});

it('can unsuspend account', function () {
    $this->account->update([
        'is_suspended' => true,
        'suspension_reason' => 'Test',
        'suspended_until' => now()->addDays(7),
    ]);

    $account = $this->account->fresh();
    $result = $account->unsuspend();

    expect($result)->toBeTrue();
    expect($account->fresh()->is_suspended)->toBeFalse();
    expect($account->fresh()->suspension_reason)->toBeNull();
    expect($account->fresh()->suspended_until)->toBeNull();
});

it('can check if hwid can be reset', function () {
    $this->account->update(['hwid_last_reset_at' => null]);

    expect($this->account->canResetHwid())->toBeTrue();

    $this->account->update(['hwid_last_reset_at' => now()]);
    expect($this->account->fresh()->canResetHwid())->toBeFalse();

    $this->account->update(['hwid_last_reset_at' => now()->subHours(73)]);
    expect($this->account->fresh()->canResetHwid())->toBeTrue();
});

it('can increment hwid reset count', function () {
    $initialCount = $this->account->hwid_reset_count;

    $result = $this->account->incrementHwidResetCount();

    expect($result)->toBeTrue();
    expect($this->account->fresh()->hwid_reset_count)->toBe($initialCount + 1);
    expect($this->account->hwid_last_reset_at)->not->toBeNull();
});

it('can get bound device count', function () {
    AccountDevice::factory()->create([
        'account_id' => $this->account->id,
        'bound_at' => now(),
        'unbound_at' => null,
    ]);

    AccountDevice::factory()->create([
        'account_id' => $this->account->id,
        'bound_at' => now(),
        'unbound_at' => now(),
    ]);

    expect($this->account->getBoundDeviceCount())->toBe(1);
});

it('can check privilege', function () {
    License::factory()->active()->create([
        'used_by' => $this->account->id,
        'privilege' => LicensePrivilege::ULTIMATE->value,
    ]);

    expect($this->account->fresh()->hasPrivilege(1))->toBeTrue();
    expect($this->account->fresh()->hasPrivilege(LicensePrivilege::ULTIMATE->value))->toBeTrue();
    expect($this->account->fresh()->hasPrivilege(5))->toBeFalse();
});

it('returns zero privilege without active license', function () {
    expect($this->account->getPrivilegeLevel())->toBe(0);
});

it('can get privilege level from active license', function () {
    License::factory()->create([
        'used_by' => $this->account->id,
        'status' => LicenseStatus::ACTIVE->value,
        'privilege' => LicensePrivilege::ULTIMATE->value,
        'expires_at' => now()->addYear(),
    ]);

    expect($this->account->fresh()->getPrivilegeLevel())->toBe(LicensePrivilege::ULTIMATE->value);
});

it('returns highest privilege from multiple licenses', function () {
    License::factory()->create([
        'used_by' => $this->account->id,
        'status' => LicenseStatus::ACTIVE->value,
        'privilege' => LicensePrivilege::STANDARD->value,
        'expires_at' => now()->addYear(),
    ]);

    License::factory()->create([
        'used_by' => $this->account->id,
        'status' => LicenseStatus::ACTIVE->value,
        'privilege' => LicensePrivilege::STAFF->value,
        'expires_at' => now()->addYear(),
    ]);

    expect($this->account->fresh()->getPrivilegeLevel())->toBe(LicensePrivilege::STAFF->value);
});

it('ignores expired licenses for privilege level', function () {
    License::factory()->create([
        'used_by' => $this->account->id,
        'status' => LicenseStatus::ACTIVE,
        'privilege' => LicensePrivilege::STAFF->value,
        'expires_at' => now()->subDay(),
    ]);

    expect($this->account->fresh()->getPrivilegeLevel())->toBe(0);
});

it('can get initials from username', function () {
    $account = Account::factory()->create(['username' => 'John Doe']);
    expect($account->initials())->toBe('JD');

    $account = Account::factory()->create(['username' => 'Alice']);
    expect($account->initials())->toBe('A');
});

it('has active scope', function () {
    Account::factory()->create(['is_suspended' => true]);
    Account::factory()->create(['is_suspended' => false]);

    $activeAccounts = Account::active()->get();

    expect($activeAccounts)->toHaveCount(2);
});

it('has suspended scope', function () {
    Account::factory()->create(['is_suspended' => true]);
    Account::factory()->create(['is_suspended' => false]);

    $suspendedAccounts = Account::suspended()->get();

    expect($suspendedAccounts)->toHaveCount(1);
});

it('can access licenses relationship', function () {
    License::factory()->create(['used_by' => $this->account->id]);

    expect($this->account->licenses)->toHaveCount(1);
});

it('can access devices relationship', function () {
    AccountDevice::factory()->create(['account_id' => $this->account->id]);

    expect($this->account->devices)->toHaveCount(1);
});
