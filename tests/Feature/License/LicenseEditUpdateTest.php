<?php

use App\Enums\LicensePrivilege;
use App\Enums\LicenseStatus;
use App\Models\Account;
use App\Models\License;

beforeEach(function () {
    $this->admin = createAdmin();
    $this->user = Account::factory()->create();
});

// --- Edit Form ---

it('admin can view the license edit form', function () {
    $license = License::factory()->unused()->create();

    $this->actingAs($this->admin)
        ->get(route('licenses.edit', $license))
        ->assertSuccessful()
        ->assertViewIs('licenses.edit')
        ->assertViewHasAll(['license', 'accounts', 'statusOptions', 'privilegeOptions']);
});

it('non-admin cannot access the license edit form', function () {
    $license = License::factory()->unused()->create();

    $this->actingAs($this->user)
        ->get(route('licenses.edit', $license))
        ->assertForbidden();
});

// --- Update ---

it('admin can update license privilege and expiry', function () {
    $license = License::factory()->active()->privilege(LicensePrivilege::STANDARD->value)->create([
        'expires_at' => now()->addDays(30),
        'notes' => null,
    ]);

    $newExpiry = now()->addYear()->format('Y-m-d');

    $this->actingAs($this->admin)
        ->patch(route('licenses.update', $license), [
            'key' => $license->key,
            'privilege' => LicensePrivilege::ULTIMATE->value,
            'status' => LicenseStatus::ACTIVE->value,
            'expires_at' => $newExpiry,
            'notes' => 'Updated by admin',
        ])
        ->assertRedirect(route('licenses.show', $license))
        ->assertSessionHas('success');

    expect($license->fresh()->privilege->value)->toBe(LicensePrivilege::ULTIMATE->value);
    expect($license->fresh()->notes)->toBe('Updated by admin');
    expect($license->fresh()->expires_at->format('Y-m-d'))->toBe($newExpiry);
});

it('admin can update license notes', function () {
    $license = License::factory()->active()->create(['expires_at' => now()->addYear(), 'notes' => null]);

    $this->actingAs($this->admin)
        ->patch(route('licenses.update', $license), [
            'key' => $license->key,
            'privilege' => $license->privilege->value,
            'status' => LicenseStatus::ACTIVE->value,
            'expires_at' => now()->addYear()->format('Y-m-d'),
            'notes' => 'Test note',
        ])
        ->assertRedirect(route('licenses.show', $license))
        ->assertSessionHas('success');

    expect($license->fresh()->notes)->toBe('Test note');
});

it('admin can change used_by on an unused license', function () {
    $license = License::factory()->unused()->create(['used_by' => null]);
    $targetUser = Account::factory()->create();

    $this->actingAs($this->admin)
        ->patch(route('licenses.update', $license), [
            'key' => $license->key,
            'privilege' => $license->privilege->value,
            'status' => LicenseStatus::UNUSED->value,
            'expires_at' => now()->addYear()->format('Y-m-d'),
            'notes' => null,
            'used_by' => $targetUser->id,
        ])
        ->assertRedirect(route('licenses.show', $license))
        ->assertSessionHas('success');

    expect($license->fresh()->used_by)->toBe($targetUser->id);
});

it('cannot change used_by on an active license', function () {
    $originalUser = Account::factory()->create();
    $license = License::factory()->active()->create([
        'used_by' => $originalUser->id,
        'expires_at' => now()->addYear(),
    ]);
    $newUser = Account::factory()->create();

    $this->actingAs($this->admin)
        ->patch(route('licenses.update', $license), [
            'key' => $license->key,
            'privilege' => $license->privilege->value,
            'status' => LicenseStatus::ACTIVE->value,
            'expires_at' => now()->addYear()->format('Y-m-d'),
            'notes' => null,
            'used_by' => $newUser->id,
        ])
        ->assertRedirect(route('licenses.show', $license));

    // used_by should NOT be changed because license is not unused
    expect($license->fresh()->used_by)->toBe($originalUser->id);
});

it('ignores attempted status changes during update and preserves original status', function () {
    $license = License::factory()->active()->create([
        'expires_at' => now()->addYear(),
    ]);

    $this->actingAs($this->admin)
        ->patch(route('licenses.update', $license), [
            'key' => $license->key,
            'privilege' => $license->privilege->value,
            'status' => LicenseStatus::REVOKED->value,
            'expires_at' => now()->addYear()->format('Y-m-d'),
            'notes' => 'status tamper attempt',
        ])
        ->assertRedirect(route('licenses.show', $license))
        ->assertSessionHas('success');

    expect($license->fresh()->status)->toBe(LicenseStatus::ACTIVE);
});

it('update validates required privilege field', function () {
    $license = License::factory()->unused()->create();

    $this->actingAs($this->admin)
        ->patch(route('licenses.update', $license), [
            'status' => LicenseStatus::UNUSED->value,
            'expires_at' => now()->addYear()->format('Y-m-d'),
        ])
        ->assertSessionHasErrors('privilege');
});

it('update validates required expires_at field', function () {
    $license = License::factory()->unused()->create();

    $this->actingAs($this->admin)
        ->patch(route('licenses.update', $license), [
            'privilege' => LicensePrivilege::STANDARD->value,
            'status' => LicenseStatus::UNUSED->value,
        ])
        ->assertSessionHasErrors('expires_at');
});

it('non-admin cannot update a license', function () {
    $license = License::factory()->unused()->create();

    $this->actingAs($this->user)
        ->patch(route('licenses.update', $license), [
            'privilege' => LicensePrivilege::STANDARD->value,
            'status' => LicenseStatus::UNUSED->value,
            'expires_at' => now()->addYear()->format('Y-m-d'),
        ])
        ->assertForbidden();
});

it('store rejects invalid privilege enum value', function () {
    $this->actingAs($this->admin)
        ->post(route('licenses.store'), [
            'privilege' => 99,
            'status' => LicenseStatus::UNUSED->value,
            'expires_at' => now()->addYear()->format('Y-m-d'),
        ])
        ->assertSessionHasErrors('privilege');
});

it('store rejects past expiration date', function () {
    $this->actingAs($this->admin)
        ->post(route('licenses.store'), [
            'privilege' => LicensePrivilege::STANDARD->value,
            'status' => LicenseStatus::UNUSED->value,
            'expires_at' => now()->subDay()->format('Y-m-d'),
        ])
        ->assertSessionHasErrors('expires_at');
});

it('store rejects malformed custom key', function () {
    $this->actingAs($this->admin)
        ->post(route('licenses.store'), [
            'key' => 'bad-key',
            'privilege' => LicensePrivilege::STANDARD->value,
            'status' => LicenseStatus::UNUSED->value,
            'expires_at' => now()->addYear()->format('Y-m-d'),
        ])
        ->assertSessionHasErrors('key');
});

it('store rejects duplicate custom key', function () {
    $license = License::factory()->create();

    $this->actingAs($this->admin)
        ->post(route('licenses.store'), [
            'key' => strtolower($license->key),
            'privilege' => LicensePrivilege::STANDARD->value,
            'status' => LicenseStatus::UNUSED->value,
            'expires_at' => now()->addYear()->format('Y-m-d'),
        ])
        ->assertSessionHasErrors('key');
});

it('store rejects non existent used_by account id', function () {
    $this->actingAs($this->admin)
        ->post(route('licenses.store'), [
            'privilege' => LicensePrivilege::STANDARD->value,
            'status' => LicenseStatus::UNUSED->value,
            'expires_at' => now()->addYear()->format('Y-m-d'),
            'used_by' => 999999,
        ])
        ->assertSessionHasErrors('used_by');
});

it('update rejects key that does not exist in licenses table', function () {
    $license = License::factory()->unused()->create();

    $this->actingAs($this->admin)
        ->patch(route('licenses.update', $license), [
            'key' => 'ABCDE-12345-ABCDE-FGHIJ-KLMNO',
            'privilege' => LicensePrivilege::STANDARD->value,
            'status' => LicenseStatus::UNUSED->value,
            'expires_at' => now()->addYear()->format('Y-m-d'),
        ])
        ->assertSessionHasErrors('key');
});
