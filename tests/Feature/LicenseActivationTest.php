<?php

use App\Enums\LicenseStatus;
use App\Models\Account;
use App\Models\License;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can activate license by key', function () {
    // Create an account
    $account = Account::factory()->create();

    // Create an unused license with privilege level 1 (standard)
    $license = License::factory()->create([
        'status' => LicenseStatus::UNUSED->value,
        'privilege' => 1, // standard
        'expires_at' => now()->addYear(),
    ]);

    // Act as the user
    $this->actingAs($account);

    // Attempt to activate the license by key
    $response = $this->post(route('licenses.activate-by-key'), [
        'license_key' => $license->key,
    ]);

    // Assert successful activation
    $response->assertRedirect(route('licenses.show', $license));
    $response->assertSessionHas('success', 'License activated successfully!');

    // Verify license is now active and assigned to the user
    $license->refresh();
    expect($license->status)->toBe(LicenseStatus::ACTIVE);
    expect($license->used_by)->toBe($account->id);
});

it('cannot activate already active license', function () {
    // Create an account
    $account = Account::factory()->create();

    // Create an active license assigned to another account
    $license = License::factory()->create([
        'status' => LicenseStatus::ACTIVE->value,
        'used_by' => Account::factory()->create()->id,
        'expires_at' => now()->addYear(),
    ]);

    // Act as the user
    $this->actingAs($account);

    // Attempt to activate the license by key
    $response = $this->post(route('licenses.activate-by-key'), [
        'license_key' => $license->key,
    ]);

    // Assert validation error
    $response->assertRedirect();
    $response->assertSessionHasErrors('license_key', 'License is already active and in use by another account.');
});

it('cannot activate expired license', function () {
    // Create an account
    $account = Account::factory()->create();

    // Create an expired unused license
    $license = License::factory()->create([
        'status' => LicenseStatus::UNUSED->value,
        'expires_at' => now()->subDay(),
    ]);

    // Act as the user
    $this->actingAs($account);

    // Attempt to activate the license by key
    $response = $this->post(route('licenses.activate-by-key'), [
        'license_key' => $license->key,
    ]);

    // Assert validation error
    $response->assertRedirect();
    $response->assertSessionHasErrors('license_key', 'License has expired.');
});

it('validates license key format', function () {
    // Create an account
    $account = Account::factory()->create();

    // Act as the user
    $this->actingAs($account);

    // Attempt to activate with invalid format
    $response = $this->post(route('licenses.activate-by-key'), [
        'license_key' => 'INVALID-KEY-FORMAT',
    ]);

    // Assert validation error
    $response->assertRedirect();
    $response->assertSessionHasErrors('license_key');
});

it('cannot activate license of same privilege level', function () {
    // Create an account
    $account = Account::factory()->create();

    // Create an active basic license for the account
    License::factory()->create([
        'status' => LicenseStatus::ACTIVE->value,
        'privilege' => 1, // basic
        'used_by' => $account->id,
        'expires_at' => now()->addYear(),
    ]);

    // Create another unused basic license (same privilege)
    $license = License::factory()->create([
        'status' => LicenseStatus::UNUSED->value,
        'privilege' => 1, // basic
        'expires_at' => now()->addYear(),
    ]);

    // Act as the user
    $this->actingAs($account);

    // Attempt to activate the second license
    $response = $this->post(route('licenses.activate-by-key'), [
        'license_key' => $license->key,
    ]);

    // Assert validation error
    $response->assertRedirect();
    $response->assertSessionHasErrors('license_key', 'You already have an active basic license. You cannot activate another license of the same level.');
});

it('cannot downgrade to lower privilege license', function () {
    // Create an account
    $account = Account::factory()->create();

    // Create an active regular license for the account
    License::factory()->create([
        'status' => LicenseStatus::ACTIVE->value,
        'privilege' => 2, // regular
        'used_by' => $account->id,
        'expires_at' => now()->addYear(),
    ]);

    // Create an unused basic license (lower privilege)
    $license = License::factory()->create([
        'status' => LicenseStatus::UNUSED->value,
        'privilege' => 1, // basic
        'expires_at' => now()->addYear(),
    ]);

    // Act as the user
    $this->actingAs($account);

    // Attempt to activate the basic license
    $response = $this->post(route('licenses.activate-by-key'), [
        'license_key' => $license->key,
    ]);

    // Assert validation error
    $response->assertRedirect();
    $response->assertSessionHasErrors('license_key', 'You already have an active regular license. You cannot downgrade to basic.');
});

it('can upgrade to higher privilege license', function () {
    // Create an account
    $account = Account::factory()->create();

    // Create an active basic license for the account
    License::factory()->create([
        'status' => LicenseStatus::ACTIVE->value,
        'privilege' => 1, // basic
        'used_by' => $account->id,
        'expires_at' => now()->addYear(),
    ]);

    // Create an unused ultimate license (higher privilege)
    $license = License::factory()->create([
        'status' => LicenseStatus::UNUSED->value,
        'privilege' => 3, // ultimate
        'expires_at' => now()->addYear(),
    ]);

    // Act as the user
    $this->actingAs($account);

    // Attempt to activate the ultimate license
    $response = $this->post(route('licenses.activate-by-key'), [
        'license_key' => $license->key,
    ]);

    // Assert successful upgrade
    $response->assertRedirect(route('licenses.show', $license));
    $response->assertSessionHas('success', 'License activated successfully!');

    // Verify the upgrade worked
    $license->refresh();
    expect($license->status)->toBe(LicenseStatus::ACTIVE);
    expect($license->used_by)->toBe($account->id);
    expect($account->getPrivilegeLevel())->toBe(3); // ultimate
});
