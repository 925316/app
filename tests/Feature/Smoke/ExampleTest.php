<?php

use App\Models\Account;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('guest homepage renders the marketing experience with theme toggle support', function () {
    get('/')
        ->assertOk()
        ->assertSeeText('Operational control for licenses, devices, packages, and logs.')
        ->assertSeeText('Laravel-backed license administration for licenses, devices, package releases, sessions, and audit visibility.')
        ->assertSeeText('Client-supplied HWID is stored server-side as a SHA-256 hash for binding and recovery workflows.')
        ->assertSeeText('Stable and dev packages stay visible through authenticated update checks and signed release decisions.')
        ->assertSeeText('Heartbeat-driven sessions and audit trails stay visible so operators can explain device and license activity.')
        ->assertSeeText('Current response verification uses the returned signature and meta.signature metadata to verify the server\'s current signed payload.')
        ->assertSeeText('Developer guide: CPP_CLIENT_VERIFICATION.md in the repository root')
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
        ->assertDontSee('href="CPP_CLIENT_VERIFICATION.md"', false)
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
