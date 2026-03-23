<?php

use App\Models\Account;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

test('reset password link screen can be rendered', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
});

test('authenticated users are redirected away from forgot password screen', function () {
    $user = Account::factory()->create();

    $this->actingAs($user)
        ->get('/forgot-password')
        ->assertRedirect(route('dashboard', absolute: false));
});

test('reset password link can be requested', function () {
    Notification::fake();

    $user = Account::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class);
});

test('reset password screen can be rendered', function () {
    Notification::fake();

    $user = Account::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
        $response = $this->get('/reset-password/'.$notification->token);

        $response->assertStatus(200);

        return true;
    });
});

test('password can be reset with valid token', function () {
    Notification::fake();

    $user = Account::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $originalRememberToken = $user->remember_token;

        $response = $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
        expect($user->fresh()->remember_token)->not->toBe($originalRememberToken);

        return true;
    });
});

test('password reset fails with invalid token', function () {
    $user = Account::factory()->create();
    $oldPasswordHash = $user->password;

    $response = $this->from('/reset-password/invalid-token')->post('/reset-password', [
        'token' => 'invalid-token',
        'email' => $user->email,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertSessionHasErrors('email');
    expect($user->fresh()->password)->toBe($oldPasswordHash);
});
