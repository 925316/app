<?php

use App\Enums\LicensePrivilege;
use App\Enums\LicenseStatus;
use App\Models\Account;
use App\Models\License;

beforeEach(function () {
    $this->account = Account::factory()->create();
});

it('can check if license is expired', function () {
    $license = License::factory()->expired()->create();
    expect($license->isExpired())->toBeTrue();

    $license = License::factory()->active()->create(['expires_at' => now()->addYear()]);
    expect($license->isExpired())->toBeFalse();
});

it('can check if license is active', function () {
    $license = License::factory()->active()->create(['expires_at' => now()->addYear()]);
    expect($license->isActive())->toBeTrue();

    $license = License::factory()->suspended()->create();
    expect($license->isActive())->toBeFalse();
});

it('can check if license is suspended', function () {
    $license = License::factory()->suspended()->create();
    expect($license->isSuspended())->toBeTrue();

    $license = License::factory()->unused()->create();
    expect($license->isSuspended())->toBeFalse();
});

it('can check if license is unused', function () {
    $license = License::factory()->unused()->create();
    expect($license->isUnused())->toBeTrue();

    $license = License::factory()->active()->create();
    expect($license->isUnused())->toBeFalse();
});

it('can check if license is upgraded', function () {
    $license = License::factory()->upgraded()->create();
    expect($license->isUpgraded())->toBeTrue();
});

it('can check if license is revoked', function () {
    $license = License::factory()->revoked()->create();
    expect($license->isRevoked())->toBeTrue();
});

it('can calculate days until expiry', function () {
    $license = License::factory()->active()->create(['expires_at' => now()->addDays(30)]);
    expect($license->daysUntilExpiry())->toBeBetween(29, 31);

    $license = License::factory()->expired()->create();
    expect($license->daysUntilExpiry())->toBe(0);
});

it('can activate license', function () {
    $license = License::factory()->unused()->create();

    $result = $license->activate($this->account->id, '192.168.1.1');

    expect($result)->toBeTrue();
    expect($license->fresh()->status)->toBe(LicenseStatus::ACTIVE);
    expect($license->used_by)->toBe($this->account->id);
    expect($license->activated_at)->not->toBeNull();
});

it('cannot activate non-unused license', function () {
    $license = License::factory()->active()->create();

    $result = $license->activate($this->account->id);

    expect($result)->toBeFalse();
});

it('can check if can activate', function () {
    $license = License::factory()->unused()->create();
    expect($license->canActivate())->toBeTrue();

    $license = License::factory()->active()->create();
    expect($license->canActivate())->toBeFalse();
});

it('has active scope', function () {
    License::factory()->active()->create();
    License::factory()->unused()->create();

    $activeLicenses = License::active()->get();

    expect($activeLicenses)->toHaveCount(1);
});

it('has unused scope', function () {
    License::factory()->unused()->create();
    License::factory()->unused()->create();

    $unusedLicenses = License::unused()->get();

    expect($unusedLicenses)->toHaveCount(2);
});

it('has suspended scope', function () {
    License::factory()->suspended()->create();
    License::factory()->active()->create();

    $suspendedLicenses = License::suspended()->get();

    expect($suspendedLicenses)->toHaveCount(1);
});

it('has expired scope', function () {
    License::factory()->expired()->create();
    License::factory()->active()->create();

    $expiredLicenses = License::expired()->get();

    expect($expiredLicenses)->toHaveCount(1);
});

it('has privilege scope', function () {
    License::factory()->privilege(LicensePrivilege::ULTIMATE->value)->create();
    License::factory()->privilege(LicensePrivilege::STANDARD->value)->create();

    $ultimateLicenses = License::privilege(LicensePrivilege::ULTIMATE->value)->get();

    expect($ultimateLicenses)->toHaveCount(1);
});

it('has valid scope', function () {
    License::factory()->active()->create(['expires_at' => now()->addYear()]);
    License::factory()->active()->create(['expires_at' => now()->subDay()]);
    License::factory()->unused()->create();

    $validLicenses = License::valid()->get();

    expect($validLicenses)->toHaveCount(1);
});

it('has expiring soon scope', function () {
    License::factory()->active()->create(['expires_at' => now()->addDays(5)]);
    License::factory()->active()->create(['expires_at' => now()->addDays(30)]);

    $expiringLicenses = License::expiringSoon(7)->get();

    expect($expiringLicenses)->toHaveCount(1);
});

it('has for account scope', function () {
    License::factory()->create(['used_by' => $this->account->id]);
    License::factory()->create(['used_by' => null]);

    $accountLicenses = License::forAccount($this->account->id)->get();

    expect($accountLicenses)->toHaveCount(1);
});

it('can access account relationship', function () {
    $license = License::factory()->create(['used_by' => $this->account->id]);

    expect($license->account->id)->toBe($this->account->id);
});

it('can check has active license for account', function () {
    expect(License::hasActiveLicense($this->account->id))->toBeFalse();

    License::factory()->active()->create(['used_by' => $this->account->id]);

    expect(License::hasActiveLicense($this->account->id))->toBeTrue();
});

it('can get active license for account', function () {
    $license = License::factory()->active()->create(['used_by' => $this->account->id]);

    $found = License::getActiveLicense($this->account->id);

    expect($found->id)->toBe($license->id);
});

it('returns null when no active license for account', function () {
    $found = License::getActiveLicense($this->account->id);

    expect($found)->toBeNull();
});

it('can get status text attribute', function () {
    $license = License::factory()->unused()->create();
    expect($license->status_text)->toBe('unused');

    $license = License::factory()->active()->create();
    expect($license->status_text)->toBe('active');
});

it('can get privilege text attribute', function () {
    $license = License::factory()->privilege(LicensePrivilege::ULTIMATE->value)->create();
    expect($license->privilege_text)->toBe('ultimate');
});

it('auto uppercases license key', function () {
    $license = License::factory()->create(['key' => 'abcde-12345-abcde-12345-abcde']);

    expect($license->key)->toBe('ABCDE-12345-ABCDE-12345-ABCDE');
});
