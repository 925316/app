<?php

use App\Enums\LicensePrivilege;
use App\Enums\LicenseStatus;
use App\Models\Account;
use App\Models\License;

beforeEach(function () {
    $this->admin = createAdmin();
    $this->user = Account::factory()->create();
});

it('admin can view any license details', function () {
    $license = License::factory()->active()->create([
        'used_by' => $this->user->id,
        'expires_at' => now()->addYear(),
    ]);

    $this->actingAs($this->admin)
        ->get(route('licenses.show', $license))
        ->assertSuccessful();
});

it('user can view their own license', function () {
    $license = License::factory()->active()->create([
        'used_by' => $this->user->id,
        'expires_at' => now()->addYear(),
    ]);

    $this->actingAs($this->user)
        ->get(route('licenses.show', $license))
        ->assertSuccessful();
});

it('user cannot view another users license', function () {
    $otherUser = Account::factory()->create();
    $license = License::factory()->active()->create([
        'used_by' => $otherUser->id,
        'expires_at' => now()->addYear(),
    ]);

    $this->actingAs($this->user)
        ->get(route('licenses.show', $license))
        ->assertForbidden();
});

it('admin can view license create form', function () {
    $this->actingAs($this->admin)
        ->get(route('licenses.create'))
        ->assertSuccessful();
});

it('admin can create a license with auto-generated key', function () {
    $this->actingAs($this->admin)
        ->post(route('licenses.store'), [
            'privilege' => LicensePrivilege::STANDARD->value,
            'status' => LicenseStatus::UNUSED->value,
            'expires_at' => now()->addYear()->format('Y-m-d'),
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(License::where('privilege', LicensePrivilege::STANDARD->value)
        ->where('status', LicenseStatus::UNUSED->value)
        ->exists()
    )->toBeTrue();
});

it('admin can create a license with a specific key', function () {
    $customKey = 'MYKEY-12345-ABCDE-FGHIJ-KLMNO';

    $this->actingAs($this->admin)
        ->post(route('licenses.store'), [
            'key' => $customKey,
            'privilege' => LicensePrivilege::ULTIMATE->value,
            'status' => LicenseStatus::UNUSED->value,
            'expires_at' => now()->addYear()->format('Y-m-d'),
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(License::where('key', $customKey)->exists())->toBeTrue();
});

it('admin can view license edit form', function () {
    $license = License::factory()->unused()->create();

    $this->actingAs($this->admin)
        ->get(route('licenses.edit', $license))
        ->assertSuccessful();
});

it('admin can suspend an active license', function () {
    $license = License::factory()->active()->create(['expires_at' => now()->addYear()]);

    $this->actingAs($this->admin)
        ->post(route('licenses.suspend', $license), ['suspension_reason' => 'Suspicious activity'])
        ->assertRedirect(route('licenses.show', $license))
        ->assertSessionHas('success');

    expect($license->fresh()->status)->toBe(LicenseStatus::SUSPENDED);
    expect($license->fresh()->suspended_at)->not->toBeNull();
});

it('admin can reactivate a suspended license', function () {
    $license = License::factory()->suspended()->create(['expires_at' => now()->addYear()]);

    $this->actingAs($this->admin)
        ->post(route('licenses.reactivate', $license))
        ->assertRedirect(route('licenses.show', $license))
        ->assertSessionHas('success');

    expect($license->fresh()->status)->toBe(LicenseStatus::ACTIVE);
    expect($license->fresh()->suspended_at)->toBeNull();
});

it('admin can revoke a license', function () {
    $license = License::factory()->active()->create(['expires_at' => now()->addYear()]);

    $this->actingAs($this->admin)
        ->post(route('licenses.revoke', $license), ['revocation_reason' => 'Policy violation'])
        ->assertRedirect(route('licenses.show', $license))
        ->assertSessionHas('success');

    expect($license->fresh()->status)->toBe(LicenseStatus::REVOKED);
    expect($license->fresh()->notes)->toBe('Policy violation');
});

it('admin can upgrade a license to higher privilege', function () {
    $license = License::factory()->active()->privilege(LicensePrivilege::STANDARD->value)->create([
        'expires_at' => now()->addYear(),
    ]);

    $this->actingAs($this->admin)
        ->post(route('licenses.upgrade', $license), [
            'new_privilege' => LicensePrivilege::ULTIMATE->value,
            'upgrade_notes' => 'Upgraded per request',
        ])
        ->assertRedirect(route('licenses.show', $license))
        ->assertSessionHas('success');

    expect($license->fresh()->status)->toBe(LicenseStatus::UPGRADED);
    expect($license->fresh()->privilege->value)->toBe(LicensePrivilege::ULTIMATE->value);
});

it('admin can extend license expiration', function () {
    $license = License::factory()->active()->create(['expires_at' => now()->addDays(30)]);
    $originalExpiry = $license->expires_at->copy();

    $this->actingAs($this->admin)
        ->post(route('licenses.extend', $license), ['days' => 30])
        ->assertRedirect(route('licenses.show', $license))
        ->assertSessionHas('success');

    expect($license->fresh()->expires_at->gt($originalExpiry))->toBeTrue();
});

it('admin can delete a license', function () {
    $license = License::factory()->unused()->create();

    $this->actingAs($this->admin)
        ->delete(route('licenses.destroy', $license))
        ->assertRedirect(route('licenses.index'))
        ->assertSessionHas('success');

    expect(License::find($license->id))->toBeNull();
});

it('cannot suspend an already suspended license', function () {
    $license = License::factory()->suspended()->create();

    $this->actingAs($this->admin)
        ->post(route('licenses.suspend', $license))
        ->assertSessionHasErrors();
});

it('cannot reactivate a non-suspended license', function () {
    $license = License::factory()->active()->create(['expires_at' => now()->addYear()]);

    $this->actingAs($this->admin)
        ->post(route('licenses.reactivate', $license))
        ->assertSessionHasErrors();
});

it('cannot revoke an already revoked license', function () {
    $license = License::factory()->revoked()->create();

    $this->actingAs($this->admin)
        ->post(route('licenses.revoke', $license))
        ->assertSessionHasErrors();
});
