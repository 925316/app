<?php

use App\Models\Account;
use App\Models\License;
use App\Enums\LicenseStatus;
use App\Enums\LicensePrivilege;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can access licenses through account relationship', function () {
    // Create an account
    $account = Account::factory()->create();

    // Create a license for the account
    $license = License::factory()->create([
        'used_by' => $account->id,
        'status' => LicenseStatus::ACTIVE->value,
        'privilege' => LicensePrivilege::ULTIMATE->value,
        'expires_at' => now()->addYear(),
    ]);

    // Test the relationship
    $licenses = $account->licenses;
    expect($licenses)->toHaveCount(1);
    expect($licenses->first()->id)->toBe($license->id);
});

it('can get privilege level from active license', function () {
    // Create an account
    $account = Account::factory()->create();

    // Create an active license for the account
    $license = License::factory()->create([
        'used_by' => $account->id,
        'status' => LicenseStatus::ACTIVE->value,
        'privilege' => LicensePrivilege::ULTIMATE->value,
        'expires_at' => now()->addYear(),
    ]);

    // Test the privilege level
    $privilegeLevel = $account->getPrivilegeLevel();
    expect($privilegeLevel)->toBe(LicensePrivilege::ULTIMATE->value);
});

it('returns 0 privilege level when no active license', function () {
    // Create an account
    $account = Account::factory()->create();

    // Test the privilege level without any license
    $privilegeLevel = $account->getPrivilegeLevel();
    expect($privilegeLevel)->toBe(0);
});
