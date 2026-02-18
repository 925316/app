<?php

use App\Enums\LicensePrivilege;
use App\Enums\LicenseStatus;
use App\Models\Account;
use App\Models\License;

beforeEach(function () {
    $this->account = Account::factory()->create();
});

it('user can activate an unused license directly', function () {
    $license = License::factory()->unused()->privilege(LicensePrivilege::STANDARD->value)->create([
        'expires_at' => now()->addYear(),
    ]);

    $this->actingAs($this->account)
        ->post(route('licenses.activate', $license))
        ->assertRedirect(route('licenses.show', $license))
        ->assertSessionHas('success');

    expect($license->fresh()->status)->toBe(LicenseStatus::ACTIVE);
    expect($license->fresh()->used_by)->toBe($this->account->id);
});

it('cannot directly activate an already active license', function () {
    $otherAccount = Account::factory()->create();
    $license = License::factory()->active()->create([
        'used_by' => $otherAccount->id,
        'expires_at' => now()->addYear(),
    ]);

    $this->actingAs($this->account)
        ->post(route('licenses.activate', $license))
        ->assertSessionHasErrors('license');
});

it('cannot directly activate a revoked license', function () {
    $license = License::factory()->revoked()->create();

    $this->actingAs($this->account)
        ->post(route('licenses.activate', $license))
        ->assertSessionHasErrors('license');
});

it('cannot directly activate a suspended license', function () {
    $license = License::factory()->suspended()->create();

    $this->actingAs($this->account)
        ->post(route('licenses.activate', $license))
        ->assertSessionHasErrors('license');
});

it('cannot directly activate a license at same privilege level', function () {
    License::factory()->active()->create([
        'privilege' => LicensePrivilege::STANDARD->value,
        'used_by' => $this->account->id,
        'expires_at' => now()->addYear(),
    ]);

    $newLicense = License::factory()->unused()->privilege(LicensePrivilege::STANDARD->value)->create([
        'expires_at' => now()->addYear(),
    ]);

    $this->actingAs($this->account)
        ->post(route('licenses.activate', $newLicense))
        ->assertSessionHasErrors('license');
});

it('cannot directly downgrade to lower privilege license', function () {
    License::factory()->active()->create([
        'privilege' => LicensePrivilege::ULTIMATE->value,
        'used_by' => $this->account->id,
        'expires_at' => now()->addYear(),
    ]);

    $lowerLicense = License::factory()->unused()->privilege(LicensePrivilege::STANDARD->value)->create([
        'expires_at' => now()->addYear(),
    ]);

    $this->actingAs($this->account)
        ->post(route('licenses.activate', $lowerLicense))
        ->assertSessionHasErrors('license');
});

it('can directly activate a higher privilege license as upgrade', function () {
    License::factory()->active()->create([
        'privilege' => LicensePrivilege::STANDARD->value,
        'used_by' => $this->account->id,
        'expires_at' => now()->addYear(),
    ]);

    $higherLicense = License::factory()->unused()->privilege(LicensePrivilege::ULTIMATE->value)->create([
        'expires_at' => now()->addYear(),
    ]);

    $this->actingAs($this->account)
        ->post(route('licenses.activate', $higherLicense))
        ->assertRedirect(route('licenses.show', $higherLicense))
        ->assertSessionHas('success');

    expect($higherLicense->fresh()->status)->toBe(LicenseStatus::ACTIVE);
    expect($this->account->fresh()->getPrivilegeLevel())->toBe(LicensePrivilege::ULTIMATE->value);
});

it('cannot directly activate an upgrade-type license without existing standard license', function () {
    $upgradeLicense = License::factory()->unused()->privilege(LicensePrivilege::UPGRADE->value)->create([
        'expires_at' => now()->addYear(),
    ]);

    $this->actingAs($this->account)
        ->post(route('licenses.activate', $upgradeLicense))
        ->assertSessionHasErrors('license');
});
