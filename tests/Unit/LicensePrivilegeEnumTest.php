<?php

use App\Enums\LicensePrivilege;

it('returns correct label for each privilege', function () {
    expect(LicensePrivilege::DEFAULT->getLabel())->toBe('none');
    expect(LicensePrivilege::STANDARD->getLabel())->toBe('standard');
    expect(LicensePrivilege::UPGRADE->getLabel())->toBe('upgrade');
    expect(LicensePrivilege::ULTIMATE->getLabel())->toBe('ultimate');
    expect(LicensePrivilege::TESTER->getLabel())->toBe('tester');
    expect(LicensePrivilege::STAFF->getLabel())->toBe('staff');
});

it('returns options array with all privilege cases', function () {
    $options = LicensePrivilege::options();

    expect($options)->toHaveCount(6);
    expect($options)->toHaveKey(LicensePrivilege::DEFAULT->value, 'none');
    expect($options)->toHaveKey(LicensePrivilege::STANDARD->value, 'standard');
    expect($options)->toHaveKey(LicensePrivilege::UPGRADE->value, 'upgrade');
    expect($options)->toHaveKey(LicensePrivilege::ULTIMATE->value, 'ultimate');
    expect($options)->toHaveKey(LicensePrivilege::TESTER->value, 'tester');
    expect($options)->toHaveKey(LicensePrivilege::STAFF->value, 'staff');
});

it('has correct integer values', function () {
    expect(LicensePrivilege::DEFAULT->value)->toBe(0);
    expect(LicensePrivilege::STANDARD->value)->toBe(1);
    expect(LicensePrivilege::UPGRADE->value)->toBe(2);
    expect(LicensePrivilege::ULTIMATE->value)->toBe(3);
    expect(LicensePrivilege::TESTER->value)->toBe(6);
    expect(LicensePrivilege::STAFF->value)->toBe(7);
});

it('can be created from integer value', function () {
    expect(LicensePrivilege::tryFrom(1))->toBe(LicensePrivilege::STANDARD);
    expect(LicensePrivilege::tryFrom(7))->toBe(LicensePrivilege::STAFF);
    expect(LicensePrivilege::tryFrom(99))->toBeNull();
});

it('options array keys are integers', function () {
    $options = LicensePrivilege::options();

    foreach (array_keys($options) as $key) {
        expect($key)->toBeInt();
    }
});
