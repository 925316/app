<?php

use App\Models\Account;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('authenticated users are redirected away from registration screen', function () {
    $user = Account::factory()->create();

    $this->actingAs($user)
        ->get('/register')
        ->assertRedirect(route('dashboard', absolute: false));
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'username' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('registration validates unique email', function () {
    Account::factory()->create(['email' => 'test@example.com']);

    $this->post('/register', [
        'username' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors('email');
});
