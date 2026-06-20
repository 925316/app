<?php

use App\Models\Account;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

test('login screen can be rendered', function () {
    $response = get('/login');

    $response->assertOk()
        ->assertSee('id="guest-content"', false)
        ->assertSee('data-shell-theme="atelier"', false)
        ->assertSee('shell-atelier shell-atelier--guest', false)
        ->assertSee('data-page="auth-login"', false)
        ->assertSee('data-auth-form="login"', false)
        ->assertSee('aria-labelledby="auth-panel-title"', false);
});

test('input with icon forwards caller attributes to the input while preserving base classes', function () {
    $html = Blade::render(<<<'BLADE'
        <x-input-with-icon
            id="email"
            name="email"
            type="email"
            value="0"
            placeholder=""
            required
            autofocus
            autocomplete="username"
            icon="user"
            class="ring-2"
            disabled
            readonly
            maxlength="32"
            aria-describedby="email-help"
            data-track="email"
        />
    BLADE);

    expect($html)
        ->toContain('id="email"')
        ->toContain('name="email"')
        ->toContain('type="email"')
        ->toContain('value="0"')
        ->toContain('placeholder=""')
        ->toContain('autocomplete="username"')
        ->toContain('maxlength="32"')
        ->toContain('aria-describedby="email-help"')
        ->toContain('data-track="email"')
        ->toContain('class="form-input input-with-icon block w-full ring-2"')
        ->toContain('disabled')
        ->toContain('readonly')
        ->toContain('required')
        ->toContain('autofocus');
});

test('users can authenticate using the login screen', function () {
    $user = Account::factory()->create();

    $response = post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    expect(Auth::check())->toBeTrue();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('authenticated users are redirected away from login screen', function () {
    /** @var Account $user */
    $user = Account::factory()->create();

    actingAs($user)
        ->get('/login')
        ->assertRedirect(route('dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = Account::factory()->create();

    post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    expect(Auth::check())->toBeFalse();
});

test('login is rate limited after too many failed attempts', function () {
    $user = Account::factory()->create();

    for ($i = 0; $i < 5; $i++) {
        post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');
    }

    post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('email');

    expect(Auth::check())->toBeFalse();
});

test('successful login clears previous rate limiting attempts', function () {
    $user = Account::factory()->create();

    for ($i = 0; $i < 3; $i++) {
        post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');
    }

    post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    post('/logout')->assertRedirect('/');

    for ($i = 0; $i < 3; $i++) {
        $response = post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $emailErrors = session('errors')->get('email');
        expect(implode(' ', $emailErrors))->not->toContain('Too many login attempts');
    }
});

test('users can logout', function () {
    /** @var Account $user */
    $user = Account::factory()->create();

    $response = actingAs($user)->post('/logout');

    expect(Auth::check())->toBeFalse();
    $response->assertRedirect('/');
});
