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
        ->assertSee('data-app-shell-header-copy', false)
        ->assertSee('aria-label="Primary navigation"', false)
        ->assertSee('aria-label="Dashboard"', false)
        ->assertSee('data-page="dashboard-admin"', false)
        ->assertSee('data-dashboard-summary', false)
        ->assertSee('id="app-main-content"', false);
});

it('sidebar nav link keeps an accessible name when the visible label is toggled', function () {
    $html = Blade::render('<x-sidebar-nav-link href="/dashboard" :active="true" icon="home">Dashboard</x-sidebar-nav-link>');

    expect($html)
        ->toContain('aria-current="page"')
        ->toContain('aria-label="Dashboard"')
        ->toContain('aria-hidden="true"')
        ->toContain('x-show="mobileSidebarOpen || $store.sidebar.open"');
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
        ->toContain('data-sidebar-language-trigger')
        ->toContain('data-sidebar-language-menu')
        ->toContain('x-transition.opacity.origin.bottom.right')
        ->toContain('sidebar-account-icon')
        ->toContain('sidebar-locale-select')
        ->toContain('aria-label="Log out"')
        ->toContain('sidebar-account-collapsed')
        ->toContain('sidebar-account-toggle')
        ->toContain('aria-pressed=')
        ->not->toContain('sidebar-utility-link sidebar-account-entry')
        ->not->toContain('>Log Out<');
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
        ->assertSee('data-dashboard-summary-chip', false)
        ->assertSee('data-license-state="active"', false)
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
