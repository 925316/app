<?php

use App\Enums\LicensePrivilege;
use App\Enums\LicenseStatus;
use App\Models\Account;
use App\Models\License;
use App\Services\LicenseService;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->account = Account::factory()->create();
});

it('can generate license key', function () {
    $key = LicenseService::generateLicenseKey();

    expect($key)->toMatch('/'.LicenseService::LICENSE_KEY_PATTERN.'/');
});

it('can validate license key format', function () {
    $validKey = 'ABCDE-1A2B3-AB2C3-A3B4C-K9LMN';
    expect(LicenseService::validateLicenseKeyFormat($validKey))->toBeTrue();

    $invalidKey = 'INVALID-KEY';
    expect(LicenseService::validateLicenseKeyFormat($invalidKey))->toBeFalse();
});

it('can create license', function () {
    $license = LicenseService::createLicense(LicensePrivilege::STANDARD->value);

    expect($license)->toBeInstanceOf(License::class);
    expect($license->status)->toBe(LicenseStatus::UNUSED);
    expect($license->privilege->value)->toBe(LicensePrivilege::STANDARD->value);
});

it('can create license with custom key', function () {
    $customKey = 'ABCDE-1A2B3-AB2C3-A3B4C-K9LMN';
    $license = LicenseService::createLicense(
        LicensePrivilege::STANDARD->value,
        null,
        $customKey
    );

    expect($license->key)->toBe($customKey);
});

it('can activate license', function () {
    $license = License::factory()->unused()->privilege(LicensePrivilege::STANDARD->value)->create();

    $result = LicenseService::activateLicense($license, $this->account);

    expect($result)->toBeTrue();
    expect($license->fresh()->status)->toBe(LicenseStatus::ACTIVE);
    expect($license->used_by)->toBe($this->account->id);
});

it('cannot activate already used license', function () {
    $license = License::factory()->active()->create();

    expect(fn () => LicenseService::activateLicense($license, $this->account))
        ->toThrow(ValidationException::class);
});

it('cannot activate upgrade license alone', function () {
    $license = License::factory()->unused()->privilege(LicensePrivilege::UPGRADE->value)->create();

    expect(fn () => LicenseService::activateLicense($license, $this->account))
        ->toThrow(ValidationException::class);
});

it('can suspend license', function () {
    $license = License::factory()->active()->create();

    $result = LicenseService::suspendLicense($license, 'Test suspension');

    expect($result)->toBeTrue();
    expect($license->fresh()->status)->toBe(LicenseStatus::SUSPENDED);
    expect($license->suspended_at)->not->toBeNull();
    expect($license->notes)->toBe('Test suspension');
});

it('cannot suspend non-active license', function () {
    $license = License::factory()->unused()->create();

    expect(fn () => LicenseService::suspendLicense($license))
        ->toThrow(ValidationException::class);
});

it('can reactivate suspended license', function () {
    $license = License::factory()->suspended()->create();

    $result = LicenseService::reactivateLicense($license);

    expect($result)->toBeTrue();
    expect($license->fresh()->status)->toBe(LicenseStatus::ACTIVE);
    expect($license->suspended_at)->toBeNull();
});

it('cannot reactivate non-suspended license', function () {
    $license = License::factory()->active()->create();

    expect(fn () => LicenseService::reactivateLicense($license))
        ->toThrow(ValidationException::class);
});

it('can revoke license', function () {
    $license = License::factory()->active()->create();

    $result = LicenseService::revokeLicense($license, 'Revocation reason');

    expect($result)->toBeTrue();
    expect($license->fresh()->status)->toBe(LicenseStatus::REVOKED);
    expect($license->notes)->toBe('Revocation reason');
});

it('cannot revoke already revoked license', function () {
    $license = License::factory()->revoked()->create();

    expect(fn () => LicenseService::revokeLicense($license))
        ->toThrow(ValidationException::class);
});

it('can upgrade license', function () {
    $license = License::factory()->active()->privilege(LicensePrivilege::STANDARD->value)->create();

    $result = LicenseService::upgradeLicense($license, LicensePrivilege::ULTIMATE->value, 'Upgraded');

    expect($result)->toBeTrue();
    expect($license->fresh()->status)->toBe(LicenseStatus::UPGRADED);
    expect($license->privilege->value)->toBe(LicensePrivilege::ULTIMATE->value);
});

it('cannot upgrade non-active license', function () {
    $license = License::factory()->unused()->create();

    expect(fn () => LicenseService::upgradeLicense($license, LicensePrivilege::ULTIMATE->value))
        ->toThrow(ValidationException::class);
});

it('can extend license expiration', function () {
    $license = License::factory()->active()->create(['expires_at' => now()->addDays(30)]);
    $originalExpiry = $license->expires_at->copy();

    $result = LicenseService::extendLicenseExpiration($license, 30);

    expect($result)->toBeTrue();
    $newExpiry = $license->fresh()->expires_at;
    expect((int) $originalExpiry->diffInDays($newExpiry))->toBe(30);
});

it('cannot extend expired license', function () {
    $license = License::factory()->expired()->create();

    expect(fn () => LicenseService::extendLicenseExpiration($license, 30))
        ->toThrow(ValidationException::class);
});

it('cannot extend revoked license', function () {
    $license = License::factory()->revoked()->create();

    expect(fn () => LicenseService::extendLicenseExpiration($license, 30))
        ->toThrow(ValidationException::class);
});

it('can check if license is valid', function () {
    $license = License::factory()->unused()->create();

    expect(LicenseService::isLicenseValid($license->key))->toBeTrue();
});

it('returns false for non-existent license key', function () {
    expect(LicenseService::isLicenseValid('NONEX-ISTEN-TKEY-00000-00000'))->toBeFalse();
});

it('can get license by key', function () {
    $license = License::factory()->create();

    $found = LicenseService::getLicenseByKey($license->key);

    expect($found->id)->toBe($license->id);
});

it('returns null for non-existent key', function () {
    $found = LicenseService::getLicenseByKey('NONEX-ISTEN-TKEY-00000-00000');

    expect($found)->toBeNull();
});

it('can get licenses for account', function () {
    License::factory()->create(['used_by' => $this->account->id]);
    License::factory()->create(['used_by' => $this->account->id]);
    License::factory()->create(['used_by' => null]);

    $licenses = LicenseService::getLicensesForAccount($this->account->id);

    expect($licenses)->toHaveCount(2);
});

it('can get active license for account', function () {
    License::factory()->active()->create(['used_by' => $this->account->id]);
    License::factory()->unused()->create(['used_by' => $this->account->id]);

    $activeLicense = LicenseService::getActiveLicenseForAccount($this->account->id);

    expect($activeLicense)->not->toBeNull();
    expect($activeLicense->status)->toBe(LicenseStatus::ACTIVE);
});

it('can check if account can activate license', function () {
    expect(LicenseService::canAccountActivateLicense($this->account->id))->toBeTrue();

    License::factory()->active()->create(['used_by' => $this->account->id]);

    expect(LicenseService::canAccountActivateLicense($this->account->id))->toBeFalse();
});

it('can get license status history', function () {
    $license = License::factory()->active()->create([
        'activated_at' => now()->subDays(30),
        'expires_at' => now()->addDays(335),
    ]);

    $history = LicenseService::getLicenseStatusHistory($license);

    expect($history['current_status'])->toBe('active');
    expect($history['activated_at'])->not->toBeNull();
    expect($history['days_until_expiry'])->toBeGreaterThan(300);
});
