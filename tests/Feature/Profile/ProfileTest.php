<?php

use App\Models\Account;

test('profile page is displayed', function () {
    $user = Account::factory()->verified()->create();

    $response = $this
        ->actingAs($user)
        ->get('/profile');

    $response->assertOk()
        ->assertSee('role="dialog"', false)
        ->assertSee('aria-modal="true"', false);
});

test('non-admin cannot update username and email', function () {
    $user = Account::factory()->verified()->create();
    $originalUsername = $user->username;
    $originalEmail = $user->email;

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'username' => 'Test Account',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    // Non-admin should not be able to update username and email
    $this->assertSame($originalUsername, $user->username);
    $this->assertSame($originalEmail, $user->email);
});

test('admin can update username and email', function () {
    $user = createAdmin();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'username' => 'Test Account',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('Test Account', $user->username);
    $this->assertSame('test@example.com', $user->email);
    $this->assertNull($user->email_verified_at);
});

test('admin can update locale without requiring username and email', function () {
    $user = createAdmin();

    $response = $this
        ->actingAs($user)
        ->patch('/profile/locale', [
            'locale' => 'ko',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/')
        ->assertSessionHas('status', 'locale-updated');

    $response->assertCookie((string) config('app.locale_cookie_name', 'locale'), 'ko');
});

test('selected locale is applied on subsequent profile request', function () {
    $user = createAdmin();

    $cookieName = (string) config('app.locale_cookie_name', 'locale');
    $sessionKey = (string) config('app.locale_session_key', 'locale');

    $this->actingAs($user)
        ->patch('/profile/locale', [
            'locale' => 'ko',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/')
        ->assertSessionHas('status', 'locale-updated')
        ->assertSessionHas($sessionKey, 'ko')
        ->assertCookie($cookieName, 'ko');

    $this->actingAs($user)
        ->withCookie($cookieName, 'ko')
        ->get('/profile')
        ->assertOk()
        ->assertSee('option value="ko" selected', false);
});

test('session locale takes precedence over browser preferred language', function () {
    $user = createAdmin();

    $sessionKey = (string) config('app.locale_session_key', 'locale');

    $this->actingAs($user)
        ->withHeader('Accept-Language', 'zh-CN,zh;q=0.9,en;q=0.8')
        ->withSession([$sessionKey => 'ja'])
        ->get('/profile')
        ->assertOk()
        ->assertSee('option value="ja" selected', false);
});

test('selected english locale remains selected even when browser prefers chinese', function () {
    $user = createAdmin();

    $this->actingAs($user)
        ->withHeader('Accept-Language', 'zh-CN,zh;q=0.9,en;q=0.8')
        ->patch('/profile/locale', [
            'locale' => 'en',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/')
        ->assertSessionHas('status', 'locale-updated');

    $this->actingAs($user)
        ->withHeader('Accept-Language', 'zh-CN,zh;q=0.9,en;q=0.8')
        ->get('/profile')
        ->assertOk()
        ->assertSee('option value="en" selected', false);
});

test('locale update redirects back to the originating page', function () {
    $user = createAdmin();
    $sessionKey = (string) config('app.locale_session_key', 'locale');
    $cookieName = (string) config('app.locale_cookie_name', 'locale');

    $response = $this->actingAs($user)
        ->from(route('dashboard'))
        ->patch('/profile/locale', [
            'locale' => 'ja',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('status', 'locale-updated')
        ->assertSessionHas($sessionKey, 'ja')
        ->assertCookie($cookieName, 'ja');
});

test('email verification status is unchanged when the email address is unchanged for non-admin', function () {
    $user = Account::factory()->verified()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'username' => 'Test Account',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    // Email unchanged, so verification status should remain
    $this->assertNotNull($user->refresh()->email_verified_at);
});

test('non-admin cannot mass assign sensitive account fields through profile update payload', function () {
    $user = Account::factory()->verified()->create([
        'is_suspended' => false,
        'suspension_reason' => null,
        'hwid_reset_count' => 0,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->patch('/profile', [
            'is_suspended' => true,
            'suspension_reason' => 'tamper',
            'hwid_reset_count' => 99,
            'email_verified_at' => now()->subDay()->toISOString(),
            'username' => 'hacker_name',
            'email' => 'hacker@example.com',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();
    expect($user->is_suspended)->toBeFalse();
    expect($user->suspension_reason)->toBeNull();
    expect($user->hwid_reset_count)->toBe(0);
    expect($user->username)->not->toBe('hacker_name');
    expect($user->email)->not->toBe('hacker@example.com');
});

test('admin cannot update profile email to a duplicate email', function () {
    $admin = createAdmin();
    $existing = Account::factory()->verified()->create(['email' => 'duplicate@example.com']);

    $this->actingAs($admin)
        ->patch('/profile', [
            'username' => $admin->username,
            'email' => $existing->email,
        ])
        ->assertSessionHasErrors('email');
});

test('profile locale update rejects unsupported locale', function () {
    $user = createAdmin();

    $this->actingAs($user)
        ->patch('/profile/locale', [
            'locale' => 'xx',
        ])
        ->assertSessionHasErrors('locale');
});

test('unverified user is redirected from profile routes', function () {
    $user = Account::factory()->unverified()->create();

    $this->actingAs($user)
        ->get('/profile')
        ->assertRedirect(route('verification.notice'));

    $this->actingAs($user)
        ->patch('/profile', [
            'username' => 'attempt',
            'email' => 'attempt@example.com',
        ])
        ->assertRedirect(route('verification.notice'));

    $this->actingAs($user)
        ->patch('/profile/locale', [
            'locale' => 'en',
        ])
        ->assertRedirect(route('verification.notice'));
});

test('user can delete their account', function () {
    $user = Account::factory()->verified()->create();

    $response = $this
        ->actingAs($user)
        ->delete('/profile', [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    $this->assertNull($user->fresh());
});

test('correct password must be provided to delete account', function () {
    $user = Account::factory()->verified()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->delete('/profile', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrorsIn('userDeletion', 'password')
        ->assertRedirect('/profile');

    $this->assertNotNull($user->fresh());
});
