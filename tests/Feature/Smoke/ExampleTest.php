<?php

use App\Models\Account;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('guest homepage renders the marketing experience with theme toggle support', function () {
    get('/')
        ->assertOk()
        ->assertSeeText('Operational control for licenses, devices, packages, and logs.')
        ->assertSeeText('Sign In')
        ->assertSeeText('Create Account')
        ->assertSeeText('Signal Board')
        ->assertSeeText('Four surfaces. One command floor.')
        ->assertSeeText('Public homepage · atelier operational landing page')
        ->assertSee("x-bind:aria-label=\"dark ? 'Switch to light mode' : 'Switch to dark mode'\"", false)
        ->assertSee('x-bind:aria-pressed="dark ? \'true\' : \'false\'"', false)
        ->assertSee("const savedTheme = localStorage.getItem('theme');", false)
        ->assertSee("const hasSavedTheme = savedTheme === 'dark' || savedTheme === 'light';", false)
        ->assertSee(': true;', false)
        ->assertDontSee("const savedTheme = localStorage.getItem('theme') ?? 'dark';", false)
        ->assertDontSee("if (localStorage.getItem('theme') === null)", false)
        ->assertSee('x-data="landingSignalBoard({', false)
        ->assertSee('landing-toggle-shell', false)
        ->assertSee('landing-rainbow-bar', false)
        ->assertDontSee('document.addEventListener(\'alpine:init\'', false);
});

test('authenticated homepage swaps guest CTAs for the dashboard action and still shows the theme toggle', function () {
    $account = Account::factory()->active()->verified()->create();

    actingAs($account)
        ->get('/')
        ->assertOk()
        ->assertSeeText('Open Dashboard')
        ->assertDontSeeText('Sign In')
        ->assertDontSeeText('Create Account')
        ->assertSee("x-bind:aria-label=\"dark ? 'Switch to light mode' : 'Switch to dark mode'\"", false)
        ->assertSee('href="'.route('dashboard').'"', false);
});
