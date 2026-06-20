<?php

use App\Models\Account;
use App\Models\AccountDevice;
use App\Models\UsageStatistic;
use Illuminate\Support\Facades\Blade;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('unauthenticated user is redirected to login from dashboard', function () {
    get(route('dashboard'))
        ->assertRedirect(route('login'));
});

it('admin can access dashboard', function () {
    $admin = createAdmin();

    actingAs($admin)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('data-shell-theme="atelier"', false)
        ->assertSee('shell-atelier shell-atelier--sidebar', false)
        ->assertSee('data-app-shell-header-copy', false)
        ->assertSee('aria-label="Primary navigation"', false)
        ->assertSee('aria-label="Dashboard"', false)
        ->assertSee('data-page="dashboard-admin"', false)
        ->assertSee('data-atelier-command-deck', false)
        ->assertSee('data-atelier-spotlight', false)
        ->assertSee('data-atelier-operations-matrix', false)
        ->assertSee('data-dashboard-summary', false)
        ->assertSee('id="app-main-content"', false);
});

it('sidebar nav link keeps an accessible name when the visible label is toggled', function () {
    $html = Blade::render('<x-sidebar-nav-link href="/dashboard" :active="true" icon="home">Dashboard</x-sidebar-nav-link>');

    expect($html)
        ->toContain('aria-current="page"')
        ->toContain('aria-label="Dashboard"')
        ->toContain('aria-hidden="true"')
        ->toContain('x-show="isDesktop ? $store.sidebar.open : mobileSidebarOpen"')
        ->toContain("'sidebar-link-collapsed': isDesktop && !\$store.sidebar.open");
});

it('sidebar layout reserves desktop space with shell offsets instead of calc width coupling', function () {
    $account = createAdmin();

    actingAs($account);

    $html = Blade::render(<<<'BLADE'
        <x-app-sidebar-layout>
            <div>Dashboard content</div>
        </x-app-sidebar-layout>
    BLADE);

    expect($html)
        ->toContain("'lg:ml-72': \$store.sidebar.open")
        ->toContain("'lg:ml-16': !\$store.sidebar.open")
        ->not->toContain('lg:w-[calc(100%-16rem)]')
        ->not->toContain('lg:w-[calc(100%-4rem)]')
        ->not->toContain('lg:pl-64')
        ->not->toContain('lg:pl-16');
});

it('sidebar account section renders a bottom footer with profile row, logout icon, and theme row', function () {
    $account = createAdmin();

    actingAs($account);

    $html = view('layouts.sidebar')->render();

    expect($html)
        ->toContain('data-sidebar-account')
        ->toContain('data-sidebar-nav-panel')
        ->toContain('data-sidebar-utility-surface')
        ->toContain('data-sidebar-profile-row')
        ->toContain('data-sidebar-theme-row')
        ->toContain('data-sidebar-language-row')
        ->toContain('action="'.route('profile.update-locale').'"')
        ->toContain('name="locale"')
        ->toContain('x-ref="localeInput"')
        ->toContain('x-ref="localeTrigger"')
        ->toContain('data-sidebar-language-trigger')
        ->toContain('data-sidebar-language-menu')
        ->toContain('role="listbox"')
        ->toContain('role="option"')
        ->toContain('aria-controls="sidebar-locale-menu"')
        ->toContain('@keydown.down.prevent="openLocaleMenu()"')
        ->toContain('@keydown.enter.prevent="selectLocale(')
        ->toContain('x-transition.opacity.origin.bottom.right')
        ->toContain('sidebar-account-icon')
        ->toContain('sidebar-locale-select')
        ->toContain('aria-label="Log out"')
        ->toContain('sidebar-account-collapsed')
        ->toContain('sidebar-account-toggle')
        ->toContain('aria-pressed=')
        ->toContain('x-show="isDesktop ? $store.sidebar.open : mobileSidebarOpen"')
        ->toContain('x-show="isDesktop && !$store.sidebar.open"')
        ->not->toContain('sidebar-utility-link sidebar-account-entry')
        ->not->toContain('>Log Out<');
});

it('mobile sidebar dialog exposes an accessible name', function () {
    $account = createAdmin();

    actingAs($account);

    $html = Blade::render(<<<'BLADE'
        <x-app-sidebar-layout>
            <div>Dashboard content</div>
        </x-app-sidebar-layout>
    BLADE);

    expect($html)
        ->toContain('aria-label="'.__('Primary navigation').'"')
        ->toContain('id="app-sidebar-title"');
});

it('sidebar language menu is positioned above the trigger to avoid bottom overflow', function () {
    $account = createAdmin();

    actingAs($account);

    $html = view('layouts.sidebar')->render();

    expect($html)
        ->toContain('x-transition.opacity.origin.bottom.right');

    $css = file_get_contents(resource_path('css/modules/sidebar.css'));

    expect($css)
        ->toContain('bottom: calc(100% + 0.5rem);')
        ->not->toContain('top: calc(100% + 0.5rem);');
});

it('user with license can access dashboard', function () {
    $user = createUserWithLicense(1);

    actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful();
});

it('user without license can access dashboard', function () {
    /** @var Account $user */
    $user = Account::factory()->create();

    actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful();
});

it('admin sees the admin panel view', function () {
    $admin = createAdmin();

    actingAs($admin)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertViewIs('dashboard.admin-panel')
        ->assertSee('data-atelier-metric-rail', false)
        ->assertSee('data-dashboard-summary-chip', false)
        ->assertSee('data-dashboard-stat-grid', false)
        ->assertSee('data-dashboard-database', false)
        ->assertViewHasAll(['stats', 'recentActivity', 'databaseStatus']);
});

it('regular user sees the user panel view', function () {
    $user = createUserWithLicense(1);

    actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertViewIs('dashboard.user-panel')
        ->assertSee('data-page="dashboard-user"', false)
        ->assertSee('data-atelier-command-deck', false)
        ->assertSee('data-atelier-license-spotlight', false)
        ->assertSee('data-atelier-operations-matrix', false)
        ->assertSee('data-dashboard-summary-chip', false)
        ->assertSee('dashboard-stat-number text-3xl font-bold', false)
        ->assertSee('data-license-state="active"', false)
        ->assertDontSee('text-purple-700', false)
        ->assertDontSee('text-indigo-700', false)
        ->assertViewHasAll(['userStats', 'activeLicense', 'boundDevices', 'usageTimeFormatted']);
});

it('expired admin level license user sees user panel', function () {
    $user = createUserWithLicense(7);
    $user->forceFill(['email_verified_at' => now()])->save();

    $staffLicense = $user->licenses()->first();
    expect($staffLicense)->not->toBeNull();
    $staffLicense?->update(['expires_at' => now()->subDay()]);

    actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertViewIs('dashboard.user-panel');
});

it('user dashboard counts only currently bound devices', function () {
    $user = createUserWithLicense(1);
    $user->forceFill(['email_verified_at' => now()])->save();

    AccountDevice::factory()->create([
        'account_id' => $user->id,
        'bound_at' => now(),
        'unbound_at' => null,
    ]);

    AccountDevice::factory()->create([
        'account_id' => $user->id,
        'bound_at' => now()->subDays(3),
        'unbound_at' => now()->subDay(),
    ]);

    $response = actingAs($user)
        ->get(route('dashboard'));

    $response->assertSuccessful()->assertViewIs('dashboard.user-panel');
    expect($response->viewData('boundDevices'))->toBe(1);
});

it('user dashboard falls back to 0h when usage statistics are empty', function () {
    $user = createUserWithLicense(1);
    $user->forceFill(['email_verified_at' => now()])->save();

    UsageStatistic::query()->delete();

    $response = actingAs($user)
        ->get(route('dashboard'));

    $response->assertSuccessful()->assertViewIs('dashboard.user-panel');
    expect($response->viewData('usageTimeFormatted'))->toBe('0h');
});
