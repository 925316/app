<?php

use App\Enums\LicensePrivilege;
use App\Models\Account;

test('profile page is displayed', function () {
    $user = Account::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/profile');

    $response->assertOk();
});

test('non-admin cannot update username and email', function () {
    $user = Account::factory()->create();
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
    $user = Account::factory()->create();
    $license = $user->licenses()->create([
        'key' => 'TEST-ADMIN-KEY-12345',
        'privilege' => LicensePrivilege::STAFF,
        'status' => \App\Enums\LicenseStatus::ACTIVE,
        'expires_at' => now()->addYear(),
    ]);

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
    $user = Account::factory()->create();
    $user->licenses()->create([
        'key' => 'TEST-ADMIN-LOCALE-KEY-12345',
        'privilege' => LicensePrivilege::STAFF,
        'status' => \App\Enums\LicenseStatus::ACTIVE,
        'expires_at' => now()->addYear(),
    ]);

    $response = $this
        ->actingAs($user)
        ->patch('/profile/locale', [
            'locale' => 'ko',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile')
        ->assertSessionHas('status', 'locale-updated');

    $response->assertCookie((string) config('app.locale_cookie_name', 'locale'), 'ko');
});

test('selected locale is applied on subsequent profile request', function () {
    $user = Account::factory()->create();
    $user->licenses()->create([
        'key' => 'TEST-ADMIN-LOCALE-APPLY-KEY-12345',
        'privilege' => LicensePrivilege::STAFF,
        'status' => \App\Enums\LicenseStatus::ACTIVE,
        'expires_at' => now()->addYear(),
    ]);

    $cookieName = (string) config('app.locale_cookie_name', 'locale');
    $sessionKey = (string) config('app.locale_session_key', 'locale');

    $this->actingAs($user)
        ->patch('/profile/locale', [
            'locale' => 'ko',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile')
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
    $user = Account::factory()->create();
    $user->licenses()->create([
        'key' => 'TEST-ADMIN-LOCALE-SESSION-PRIORITY-12345',
        'privilege' => LicensePrivilege::STAFF,
        'status' => \App\Enums\LicenseStatus::ACTIVE,
        'expires_at' => now()->addYear(),
    ]);

    $sessionKey = (string) config('app.locale_session_key', 'locale');

    $this->actingAs($user)
        ->withHeader('Accept-Language', 'zh-CN,zh;q=0.9,en;q=0.8')
        ->withSession([$sessionKey => 'ja'])
        ->get('/profile')
        ->assertOk()
        ->assertSee('option value="ja" selected', false);
});

test('selected english locale remains selected even when browser prefers chinese', function () {
    $user = Account::factory()->create();
    $user->licenses()->create([
        'key' => 'TEST-ADMIN-LOCALE-EN-PERSIST-12345',
        'privilege' => LicensePrivilege::STAFF,
        'status' => \App\Enums\LicenseStatus::ACTIVE,
        'expires_at' => now()->addYear(),
    ]);

    $this->actingAs($user)
        ->withHeader('Accept-Language', 'zh-CN,zh;q=0.9,en;q=0.8')
        ->patch('/profile/locale', [
            'locale' => 'en',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile')
        ->assertSessionHas('status', 'locale-updated');

    $this->actingAs($user)
        ->withHeader('Accept-Language', 'zh-CN,zh;q=0.9,en;q=0.8')
        ->get('/profile')
        ->assertOk()
        ->assertSee('option value="en" selected', false);
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

test('user can delete their account', function () {
    $user = Account::factory()->create();

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
    $user = Account::factory()->create();

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
