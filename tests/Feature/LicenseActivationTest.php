<?php

use App\Enums\LicenseStatus;
use App\Models\Account;
use App\Models\License;

it('can activate license by key', function () {
    $account = Account::factory()->create();

    $license = License::factory()->create([
        'status' => LicenseStatus::UNUSED->value,
        'privilege' => 1,
        'expires_at' => now()->addYear(),
    ]);

    $response = $this->actingAs($account)
        ->post(route('licenses.activate-by-key'), [
            'license_key' => $license->key,
        ]);

    $response->assertRedirect(route('licenses.show', $license));
    $response->assertSessionHas('success', 'License activated successfully!');

    $license->refresh();
    expect($license->status)->toBe(LicenseStatus::ACTIVE);
    expect($license->used_by)->toBe($account->id);
});

it('cannot activate already active license', function () {
    $account = Account::factory()->create();

    $license = License::factory()->create([
        'status' => LicenseStatus::ACTIVE->value,
        'used_by' => Account::factory()->create()->id,
        'expires_at' => now()->addYear(),
    ]);

    $response = $this->actingAs($account)
        ->post(route('licenses.activate-by-key'), [
            'license_key' => $license->key,
        ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('license_key');
});

it('cannot activate expired license', function () {
    $account = Account::factory()->create();

    $license = License::factory()->create([
        'status' => LicenseStatus::UNUSED->value,
        'expires_at' => now()->subDay(),
    ]);

    $response = $this->actingAs($account)
        ->post(route('licenses.activate-by-key'), [
            'license_key' => $license->key,
        ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('license_key');
});

it('validates license key format', function () {
    $account = Account::factory()->create();

    $response = $this->actingAs($account)
        ->post(route('licenses.activate-by-key'), [
            'license_key' => 'INVALID-KEY-FORMAT',
        ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('license_key');
});

it('cannot activate license of same privilege level', function () {
    $account = Account::factory()->create();

    License::factory()->create([
        'status' => LicenseStatus::ACTIVE->value,
        'privilege' => 1,
        'used_by' => $account->id,
        'expires_at' => now()->addYear(),
    ]);

    $license = License::factory()->create([
        'status' => LicenseStatus::UNUSED->value,
        'privilege' => 1,
        'expires_at' => now()->addYear(),
    ]);

    $response = $this->actingAs($account)
        ->post(route('licenses.activate-by-key'), [
            'license_key' => $license->key,
        ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('license_key');
});

it('cannot downgrade to lower privilege license', function () {
    $account = Account::factory()->create();

    License::factory()->create([
        'status' => LicenseStatus::ACTIVE->value,
        'privilege' => 2,
        'used_by' => $account->id,
        'expires_at' => now()->addYear(),
    ]);

    $license = License::factory()->create([
        'status' => LicenseStatus::UNUSED->value,
        'privilege' => 1,
        'expires_at' => now()->addYear(),
    ]);

    $response = $this->actingAs($account)
        ->post(route('licenses.activate-by-key'), [
            'license_key' => $license->key,
        ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('license_key');
});

it('can upgrade to higher privilege license', function () {
    $account = Account::factory()->create();

    License::factory()->create([
        'status' => LicenseStatus::ACTIVE->value,
        'privilege' => 1,
        'used_by' => $account->id,
        'expires_at' => now()->addYear(),
    ]);

    $license = License::factory()->create([
        'status' => LicenseStatus::UNUSED->value,
        'privilege' => 3,
        'expires_at' => now()->addYear(),
    ]);

    $response = $this->actingAs($account)
        ->post(route('licenses.activate-by-key'), [
            'license_key' => $license->key,
        ]);

    $response->assertRedirect(route('licenses.show', $license));
    $response->assertSessionHas('success', 'License activated successfully!');

    $license->refresh();
    expect($license->status)->toBe(LicenseStatus::ACTIVE);
    expect($license->used_by)->toBe($account->id);
    expect($account->fresh()->getPrivilegeLevel())->toBe(3);
});
