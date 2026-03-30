<?php

use App\Models\Account;
use App\Models\AccountDevice;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->admin = createAdmin();
});

it('admin can view account details', function () {
    $account = Account::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('accounts.show', $account))
        ->assertSuccessful();
});

it('account detail preserves device admin controls after devices index handoff', function () {
    $account = Account::factory()->create([
        'hwid_last_reset_at' => null,
    ]);

    $device = AccountDevice::factory()->create([
        'account_id' => $account->id,
        'bound_at' => now(),
        'unbound_at' => null,
    ]);

    $this->actingAs($this->admin)
        ->get(route('accounts.show', $account))
        ->assertSuccessful()
        ->assertSee('id="account-device-'.$device->id.'"', false)
        ->assertSee('aria-label="Account device row actions"', false)
        ->assertSee(route('devices.unbind-admin', $device), false)
        ->assertSee('Reset HWID', false);
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

it('admin can create a verified account when email_verified is true', function () {
    $this->actingAs($this->admin)
        ->post(route('accounts.store'), [
            'username' => 'verifieduser1',
            'email' => 'verifieduser1@example.com',
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
            'email_verified' => true,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $created = Account::query()->where('email', 'verifieduser1@example.com')->first();
    expect($created)->not->toBeNull();
    expect($created?->email_verified_at)->not->toBeNull();
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

it('create account validates username character set', function () {
    $this->actingAs($this->admin)
        ->post(route('accounts.store'), [
            'username' => 'bad username!',
            'email' => 'valid@example.com',
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
            'email_verified' => false,
        ])
        ->assertSessionHasErrors('username');
});

it('create account validates password complexity', function () {
    $this->actingAs($this->admin)
        ->post(route('accounts.store'), [
            'username' => 'user_password_rule',
            'email' => 'complexity@example.com',
            'password' => 'alllowercase',
            'password_confirmation' => 'alllowercase',
            'email_verified' => false,
        ])
        ->assertSessionHasErrors('password');
});

it('create account validates password confirmation', function () {
    $this->actingAs($this->admin)
        ->post(route('accounts.store'), [
            'username' => 'user_confirm_rule',
            'email' => 'confirm@example.com',
            'password' => 'Password1',
            'password_confirmation' => 'Password2',
            'email_verified' => false,
        ])
        ->assertSessionHasErrors('password');
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

it('admin can update account password', function () {
    $account = Account::factory()->create();

    $this->actingAs($this->admin)
        ->patch(route('accounts.update', $account), [
            'username' => $account->username,
            'email' => $account->email,
            'password' => 'NewPassword1',
            'password_confirmation' => 'NewPassword1',
        ])
        ->assertRedirect(route('accounts.show', $account))
        ->assertSessionHas('success');

    expect(Hash::check('NewPassword1', $account->fresh()->password))->toBeTrue();
});

it('update account allows keeping existing username and email', function () {
    $account = Account::factory()->create(['username' => 'same_user', 'email' => 'same@example.com']);

    $this->actingAs($this->admin)
        ->patch(route('accounts.update', $account), [
            'username' => 'same_user',
            'email' => 'same@example.com',
            'password' => null,
            'password_confirmation' => null,
        ])
        ->assertRedirect(route('accounts.show', $account))
        ->assertSessionHas('success');

    expect($account->fresh()->username)->toBe('same_user');
    expect($account->fresh()->email)->toBe('same@example.com');
});

it('update account validates duplicate username and email', function () {
    $account = Account::factory()->create(['username' => 'owner_a', 'email' => 'owner_a@example.com']);
    $other = Account::factory()->create(['username' => 'owner_b', 'email' => 'owner_b@example.com']);

    $this->actingAs($this->admin)
        ->patch(route('accounts.update', $account), [
            'username' => $other->username,
            'email' => 'unique-to-update@example.com',
            'password' => null,
            'password_confirmation' => null,
        ])
        ->assertSessionHasErrors('username');

    $this->actingAs($this->admin)
        ->patch(route('accounts.update', $account), [
            'username' => 'still_unique_for_username',
            'email' => $other->email,
            'password' => null,
            'password_confirmation' => null,
        ])
        ->assertSessionHasErrors('email');
});

it('update account validates username character set', function () {
    $account = Account::factory()->create(['username' => 'original_name']);

    $this->actingAs($this->admin)
        ->patch(route('accounts.update', $account), [
            'username' => 'invalid username!',
            'email' => $account->email,
            'password' => null,
            'password_confirmation' => null,
        ])
        ->assertSessionHasErrors('username');
});

it('update account validates password complexity when provided', function () {
    $account = Account::factory()->create();

    $this->actingAs($this->admin)
        ->patch(route('accounts.update', $account), [
            'username' => $account->username,
            'email' => $account->email,
            'password' => 'alllowercase',
            'password_confirmation' => 'alllowercase',
        ])
        ->assertSessionHasErrors('password');
});

it('update account validates password confirmation when provided', function () {
    $account = Account::factory()->create();

    $this->actingAs($this->admin)
        ->patch(route('accounts.update', $account), [
            'username' => $account->username,
            'email' => $account->email,
            'password' => 'Password1',
            'password_confirmation' => 'Password2',
        ])
        ->assertSessionHasErrors('password');
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

it('suspend validates duration lower bound', function () {
    $account = Account::factory()->create(['is_suspended' => false]);

    $this->actingAs($this->admin)
        ->post(route('accounts.suspend', $account), [
            'duration' => 0,
        ])
        ->assertSessionHasErrors('duration');
});

it('suspend validates duration upper bound', function () {
    $account = Account::factory()->create(['is_suspended' => false]);

    $this->actingAs($this->admin)
        ->post(route('accounts.suspend', $account), [
            'duration' => 366,
        ])
        ->assertSessionHasErrors('duration');
});

it('suspend validates reason max length', function () {
    $account = Account::factory()->create(['is_suspended' => false]);

    $this->actingAs($this->admin)
        ->post(route('accounts.suspend', $account), [
            'reason' => str_repeat('a', 256),
        ])
        ->assertSessionHasErrors('reason');
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
    $account = Account::factory()->create([
        'hwid_last_reset_at' => null,
    ]);
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

it('reset hwid only unbinds currently bound devices', function () {
    $account = Account::factory()->create([
        'hwid_last_reset_at' => null,
    ]);

    $boundDevice = AccountDevice::factory()->create([
        'account_id' => $account->id,
        'bound_at' => now(),
        'unbound_at' => null,
    ]);

    $alreadyUnbound = AccountDevice::factory()->create([
        'account_id' => $account->id,
        'bound_at' => now()->subDays(2),
        'unbound_at' => now()->subDay(),
    ]);

    $this->actingAs($this->admin)
        ->post(route('accounts.reset-hwid', $account))
        ->assertRedirect(route('accounts.show', $account))
        ->assertSessionHas('success');

    expect($boundDevice->fresh()->unbound_at)->not->toBeNull();
    expect($alreadyUnbound->fresh()->unbound_at?->toISOString())->toBe($alreadyUnbound->unbound_at?->toISOString());
});

it('reset hwid is blocked when account is in cooldown window', function () {
    $account = Account::factory()->create([
        'hwid_last_reset_at' => now()->subHours(2),
    ]);

    $this->actingAs($this->admin)
        ->post(route('accounts.reset-hwid', $account))
        ->assertRedirect(route('accounts.show', $account))
        ->assertSessionHasErrors('hwid_reset');
});

it('admin can delete an account', function () {
    $account = Account::factory()->create();

    $this->actingAs($this->admin)
        ->delete(route('accounts.destroy', $account))
        ->assertRedirect(route('accounts.index'))
        ->assertSessionHas('success');

    expect(Account::find($account->id))->toBeNull();
});
