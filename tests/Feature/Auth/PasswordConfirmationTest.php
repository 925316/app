<?php

use App\Models\Account;

test('confirm password screen can be rendered', function () {
    $user = Account::factory()->create();

    $response = $this->actingAs($user)->get('/confirm-password');

    $response->assertOk()
        ->assertSee('data-page="auth-confirm-password"', false)
        ->assertSee('data-auth-form="confirm-password"', false);
});

test('guests are redirected from password confirmation routes', function () {
    $this->get('/confirm-password')->assertRedirect(route('login'));
    $this->post('/confirm-password', [
        'password' => 'password',
    ])->assertRedirect(route('login'));
});

test('password can be confirmed', function () {
    $user = Account::factory()->create();

    $response = $this->actingAs($user)->post('/confirm-password', [
        'password' => 'password',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();
    expect(session()->has('auth.password_confirmed_at'))->toBeTrue();
});

test('password is not confirmed with invalid password', function () {
    $user = Account::factory()->create();

    $response = $this->actingAs($user)->post('/confirm-password', [
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors();
});
