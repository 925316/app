<?php

use App\Models\Account;
use App\Models\AccountDevice;

beforeEach(function () {
    $this->admin = createAdmin();
});

it('admin can view account details', function () {
    $account = Account::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('accounts.show', $account))
        ->assertSuccessful();
});

it('admin can view account create form', function () {
    $this->actingAs($this->admin)
        ->get(route('accounts.create'))
        ->assertSuccessful();
});

it('admin can create a new account', function () {
    $this->actingAs($this->admin)
        ->post(route('accounts.store'), [
            'username' => 'newuser123',
            'email' => 'newuser@example.com',
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
            'email_verified' => false,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(Account::where('email', 'newuser@example.com')->exists())->toBeTrue();
});

it('create account validates unique username', function () {
    Account::factory()->create(['username' => 'existinguser']);

    $this->actingAs($this->admin)
        ->post(route('accounts.store'), [
            'username' => 'existinguser',
            'email' => 'unique@example.com',
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
            'email_verified' => false,
        ])
        ->assertSessionHasErrors('username');
});

it('create account validates unique email', function () {
    Account::factory()->create(['email' => 'taken@example.com']);

    $this->actingAs($this->admin)
        ->post(route('accounts.store'), [
            'username' => 'uniqueuser',
            'email' => 'taken@example.com',
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
            'email_verified' => false,
        ])
        ->assertSessionHasErrors('email');
});

it('admin can view account edit form', function () {
    $account = Account::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('accounts.edit', $account))
        ->assertSuccessful();
});

it('admin can update account username and email', function () {
    $account = Account::factory()->create(['username' => 'original', 'email' => 'original@example.com']);

    $this->actingAs($this->admin)
        ->patch(route('accounts.update', $account), [
            'username' => 'updated_user',
            'email' => 'updated@example.com',
            'password' => null,
            'password_confirmation' => null,
        ])
        ->assertRedirect(route('accounts.show', $account))
        ->assertSessionHas('success');

    expect($account->fresh()->username)->toBe('updated_user');
    expect($account->fresh()->email)->toBe('updated@example.com');
});

it('admin can suspend an account', function () {
    $account = Account::factory()->create(['is_suspended' => false]);

    $this->actingAs($this->admin)
        ->post(route('accounts.suspend', $account), [
            'reason' => 'Violating terms of service',
            'duration' => 7,
        ])
        ->assertRedirect(route('accounts.show', $account))
        ->assertSessionHas('success');

    expect($account->fresh()->is_suspended)->toBeTrue();
    expect($account->fresh()->suspension_reason)->toBe('Violating terms of service');
    expect($account->fresh()->suspended_until)->not->toBeNull();
});

it('admin can suspend account permanently (no duration)', function () {
    $account = Account::factory()->create(['is_suspended' => false]);

    $this->actingAs($this->admin)
        ->post(route('accounts.suspend', $account), [
            'reason' => 'Fraud detected',
        ])
        ->assertRedirect(route('accounts.show', $account))
        ->assertSessionHas('success');

    expect($account->fresh()->is_suspended)->toBeTrue();
    expect($account->fresh()->suspended_until)->toBeNull();
});

it('admin can unsuspend an account', function () {
    $account = Account::factory()->create([
        'is_suspended' => true,
        'suspension_reason' => 'Test reason',
        'suspended_until' => now()->addDays(7),
    ]);

    $this->actingAs($this->admin)
        ->post(route('accounts.unsuspend', $account))
        ->assertRedirect(route('accounts.show', $account))
        ->assertSessionHas('success');

    expect($account->fresh()->is_suspended)->toBeFalse();
    expect($account->fresh()->suspension_reason)->toBeNull();
    expect($account->fresh()->suspended_until)->toBeNull();
});

it('admin can verify email for an unverified account', function () {
    $account = Account::factory()->create(['email_verified_at' => null]);

    $this->actingAs($this->admin)
        ->post(route('accounts.verify-email', $account))
        ->assertRedirect(route('accounts.show', $account))
        ->assertSessionHas('success');

    expect($account->fresh()->email_verified_at)->not->toBeNull();
});

it('cannot verify an already verified email', function () {
    $account = Account::factory()->create(['email_verified_at' => now()]);

    $this->actingAs($this->admin)
        ->post(route('accounts.verify-email', $account))
        ->assertSessionHasErrors('email_verification');
});

it('admin can reset hwid for an account', function () {
    $account = Account::factory()->create();
    $device = AccountDevice::factory()->create([
        'account_id' => $account->id,
        'bound_at' => now(),
        'unbound_at' => null,
    ]);
    $initialResetCount = $account->hwid_reset_count;

    $this->actingAs($this->admin)
        ->post(route('accounts.reset-hwid', $account))
        ->assertRedirect(route('accounts.show', $account))
        ->assertSessionHas('success');

    expect($account->fresh()->hwid_reset_count)->toBe($initialResetCount + 1);
    expect($device->fresh()->unbound_at)->not->toBeNull();
});

it('admin can delete an account', function () {
    $account = Account::factory()->create();

    $this->actingAs($this->admin)
        ->delete(route('accounts.destroy', $account))
        ->assertRedirect(route('accounts.index'))
        ->assertSessionHas('success');

    expect(Account::find($account->id))->toBeNull();
});
