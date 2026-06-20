<?php

use Illuminate\Support\Facades\Blade;

it('shared css exposes the atelier redesign instead of the previous cinematic theme', function () {
    $tokens = file_get_contents(resource_path('css/modules/tokens.css'));
    $shell = file_get_contents(resource_path('css/modules/shell.css'));

    expect($tokens)
        ->toContain("[data-shell-theme='atelier']")
        ->not->toContain("[data-shell-theme='cinematic']");

    expect($shell)
        ->toContain('.shell-atelier__skip-link')
        ->toContain('font-family: \'Fraunces\', serif')
        ->not->toContain('.shell-cinematic__skip-link')
        ->not->toContain("[data-shell-theme='cinematic']");
});

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
        ->toContain('input-icon-slot')
        ->toContain('form-input')
        ->toContain('input-with-icon')
        ->toContain('custom-probe')
        ->not->toContain('py-3')
        ->not->toContain('pl-10')
        ->not->toContain('pr-3');
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
    $html = Blade::render('<x-modal-confirm id="confirm-demo" title="Confirm action" confirm-text="Continue" icon-name="warning" />');

    expect($html)
        ->toContain('class="btn btn-primary"')
        ->toContain('card-icon-container')
        ->not->toContain('class="btn btn-blue"')
        ->not->toContain('bg-gray-100 dark:bg-gray-700')
        ->not->toContain('text-gray-600 dark:text-gray-400');
});

it('filter box hides total summary by default even when a count is provided', function () {
    $html = Blade::render('<x-filter-box action="/accounts" title="Filter accounts" :total-count="42"><div>Content</div></x-filter-box>');

    expect($html)
        ->toContain('data-filter-box')
        ->toContain('data-atelier-filter-console')
        ->toContain('atelier-filter-console__body')
        ->not->toContain('filter-box-summary')
        ->not->toContain('Showing');
});

it('filter box can opt in to total summary when the page needs emphasis', function () {
    $html = Blade::render('<x-filter-box action="/logs" title="Filter logs" :total-count="42" :show-total="true"><div>Content</div></x-filter-box>');

    expect($html)
        ->toContain('filter-box-summary')
        ->toContain('Showing')
        ->toContain('42 total');
});

it('data table renders the atelier table stage structure for dense data pages', function () {
    $html = Blade::render('<x-data-table :headers="[\'Name\', \'Status\']"><tr><td>Demo</td><td>Active</td></tr></x-data-table>');

    expect($html)
        ->toContain('data-atelier-table-stage')
        ->toContain('data-atelier-row-density="scan"')
        ->toContain('atelier-table-stage__scroll');
});

it('filter dropdown renders hidden input state and accessible listbox semantics for filter forms', function () {
    $html = Blade::render('<x-filter-dropdown id="status" name="status" label="Status" value="active" :options="[\'\' => \'All Statuses\', \'active\' => \'Active\', \'suspended\' => \'Suspended\']" />');

    expect($html)
        ->toContain('type="hidden"')
        ->toContain('name="status"')
        ->toContain('aria-haspopup="listbox"')
        ->toContain('role="listbox"')
        ->toContain('aria-orientation="vertical"')
        ->toContain('role="option"')
        ->toContain('tabindex="-1"')
        ->toContain('filter-dropdown-trigger')
        ->toContain('filter-dropdown-option')
        ->toContain('Active')
        ->toContain('x-model="value"')
        ->not->toContain('filter-dropdown-option-icon');
});

it('filter dropdown falls back to its field id when no explicit name is provided', function () {
    $html = Blade::render('<x-filter-dropdown id="channel" label="Channel" value="stable" :options="[\'stable\' => \'Stable\', \'dev\' => \'Development\']" />');

    expect($html)
        ->toContain('name="channel"')
        ->toContain('id="channel-')
        ->toContain('-input"');
});
