<?php

use App\Enums\LicenseStatus;

it('returns correct label for each status', function () {
    expect(LicenseStatus::UNUSED->getLabel())->toBe('unused');
    expect(LicenseStatus::ACTIVE->getLabel())->toBe('active');
    expect(LicenseStatus::SUSPENDED->getLabel())->toBe('suspended');
    expect(LicenseStatus::EXPIRED->getLabel())->toBe('expired');
    expect(LicenseStatus::UPGRADED->getLabel())->toBe('upgraded');
    expect(LicenseStatus::REVOKED->getLabel())->toBe('revoked');
});

it('returns correct color for each status', function () {
    expect(LicenseStatus::UNUSED->getColor())->toContain('gray');
    expect(LicenseStatus::ACTIVE->getColor())->toContain('green');
    expect(LicenseStatus::SUSPENDED->getColor())->toContain('yellow');
    expect(LicenseStatus::EXPIRED->getColor())->toContain('red');
    expect(LicenseStatus::UPGRADED->getColor())->toContain('blue');
    expect(LicenseStatus::REVOKED->getColor())->toContain('gray');
});

it('checks if status is active', function () {
    expect(LicenseStatus::ACTIVE->isActive())->toBeTrue();
    expect(LicenseStatus::UNUSED->isActive())->toBeFalse();
    expect(LicenseStatus::SUSPENDED->isActive())->toBeFalse();
});

it('checks if status can be activated', function () {
    expect(LicenseStatus::UNUSED->canActivate())->toBeTrue();
    expect(LicenseStatus::ACTIVE->canActivate())->toBeFalse();
    expect(LicenseStatus::SUSPENDED->canActivate())->toBeFalse();
    expect(LicenseStatus::EXPIRED->canActivate())->toBeFalse();
    expect(LicenseStatus::UPGRADED->canActivate())->toBeFalse();
    expect(LicenseStatus::REVOKED->canActivate())->toBeFalse();
});

it('checks if status can be reactivated', function () {
    expect(LicenseStatus::SUSPENDED->canReactivate())->toBeTrue();
    expect(LicenseStatus::ACTIVE->canReactivate())->toBeFalse();
    expect(LicenseStatus::UNUSED->canReactivate())->toBeFalse();
    expect(LicenseStatus::EXPIRED->canReactivate())->toBeFalse();
});

it('checks if status can be upgraded', function () {
    expect(LicenseStatus::ACTIVE->canUpgrade())->toBeTrue();
    expect(LicenseStatus::UNUSED->canUpgrade())->toBeFalse();
    expect(LicenseStatus::SUSPENDED->canUpgrade())->toBeFalse();
});

it('checks if status can be suspended', function () {
    expect(LicenseStatus::ACTIVE->canSuspend())->toBeTrue();
    expect(LicenseStatus::UNUSED->canSuspend())->toBeFalse();
    expect(LicenseStatus::SUSPENDED->canSuspend())->toBeFalse();
});

it('checks if status can be revoked', function () {
    expect(LicenseStatus::UNUSED->canRevoke())->toBeTrue();
    expect(LicenseStatus::ACTIVE->canRevoke())->toBeTrue();
    expect(LicenseStatus::SUSPENDED->canRevoke())->toBeTrue();
    expect(LicenseStatus::EXPIRED->canRevoke())->toBeTrue();
    expect(LicenseStatus::UPGRADED->canRevoke())->toBeTrue();
    expect(LicenseStatus::REVOKED->canRevoke())->toBeFalse();
});

it('returns all options', function () {
    $options = LicenseStatus::options();

    expect($options)->toHaveCount(6);
    expect($options)->toHaveKey(0, 'unused');
    expect($options)->toHaveKey(1, 'active');
    expect($options)->toHaveKey(2, 'suspended');
    expect($options)->toHaveKey(3, 'expired');
    expect($options)->toHaveKey(4, 'upgraded');
    expect($options)->toHaveKey(5, 'revoked');
});

it('checks if status is invalid', function () {
    expect(LicenseStatus::EXPIRED->isInvalid())->toBeTrue();
    expect(LicenseStatus::REVOKED->isInvalid())->toBeTrue();
    expect(LicenseStatus::ACTIVE->isInvalid())->toBeFalse();
    expect(LicenseStatus::UNUSED->isInvalid())->toBeFalse();
});

it('checks if status is valid', function () {
    expect(LicenseStatus::ACTIVE->isValid())->toBeTrue();
    expect(LicenseStatus::SUSPENDED->isValid())->toBeTrue();
    expect(LicenseStatus::EXPIRED->isValid())->toBeFalse();
    expect(LicenseStatus::REVOKED->isValid())->toBeFalse();
});
