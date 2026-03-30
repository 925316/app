<?php

use Illuminate\Support\Facades\Blade;

it('sidebar nav links expose an accessible label for icon-first navigation states', function () {
    $html = Blade::render('<x-sidebar-nav-link :href="\'#\'" :active="false" icon="home">Dashboard</x-sidebar-nav-link>');

    expect($html)
        ->toContain('aria-label="Dashboard"')
        ->toContain('sidebar-link-label');
});

it('input with icon forwards arbitrary input attributes while preserving shared classes', function () {
    $html = Blade::render('<x-input-with-icon id="email" name="email" type="email" data-probe="foundation" maxlength="120" aria-describedby="email-help" class="custom-probe" />');

    expect($html)
        ->toContain('data-probe="foundation"')
        ->toContain('maxlength="120"')
        ->toContain('aria-describedby="email-help"')
        ->toContain('form-input')
        ->toContain('input-with-icon')
        ->toContain('custom-probe');
});

it('auth session status uses the shared status shell', function () {
    $html = Blade::render('<x-auth-session-status status="Verification link sent." />');

    expect($html)
        ->toContain('data-auth-session-status')
        ->toContain('section-kicker')
        ->toContain('Verification link sent.');
});

it('shared button components can render anchors for navigation actions', function () {
    $primary = Blade::render('<x-primary-button tag="a" href="/licenses">View Licenses</x-primary-button>');
    $secondary = Blade::render('<x-secondary-button tag="a" href="/accounts">View Accounts</x-secondary-button>');
    $danger = Blade::render('<x-danger-button tag="a" href="/danger">Danger Zone</x-danger-button>');

    expect($primary)
        ->toContain('<a')
        ->toContain('href="/licenses"')
        ->toContain('class="btn btn-primary"')
        ->not->toContain('<button');

    expect($secondary)
        ->toContain('<a')
        ->toContain('href="/accounts"')
        ->toContain('class="btn btn-secondary"')
        ->not->toContain('<button');

    expect($danger)
        ->toContain('<a')
        ->toContain('href="/danger"')
        ->toContain('class="btn btn-danger"')
        ->not->toContain('<button');
});

it('auth header renders the logo without the old framed badge classes by default', function () {
    $html = Blade::render('<x-auth-header title="Welcome Back" subtitle="Sign in to continue" />');

    expect($html)
        ->toContain('auth-header-mark mx-auto flex h-16 w-16 items-center justify-center')
        ->not->toContain('rounded-full shadow-lg')
        ->toContain('<svg');
});

it('modal confirm defaults blue actions to the shared primary button style', function () {
    $html = Blade::render('<x-modal-confirm id="confirm-demo" title="Confirm action" confirm-text="Continue" />');

    expect($html)
        ->toContain('class="btn btn-primary"')
        ->not->toContain('class="btn btn-blue"');
});
