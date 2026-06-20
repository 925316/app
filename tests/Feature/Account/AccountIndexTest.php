<?php

use App\Enums\LicensePrivilege;
use App\Enums\LicenseStatus;
use App\Models\Account;
use App\Models\License;

use function Pest\Laravel\actingAs;

// --- Basic Access ---

it('admin can view accounts index', function () {
    $admin = createAdmin();

    actingAs($admin)
        ->get(route('accounts.index'))
        ->assertSuccessful()
        ->assertSee('role="search"', false)
        ->assertSee('aria-label="Accounts table"', false)
        ->assertSee('data-page="accounts-index"', false)
        ->assertSee('data-atelier-data-workbench', false)
        ->assertSee('data-atelier-data-rail', false)
        ->assertSee('data-atelier-data-plane', false)
        ->assertSee('data-accounts-panel', false)
        ->assertViewIs('accounts.index')
        ->assertViewHasAll(['accounts', 'statistics', 'statusOptions', 'privilegeOptions', 'currentFilters']);
});

it('accounts index surfaces active filter chips with stable markers', function () {
    $admin = createAdmin();

    actingAs($admin)
        ->get(route('accounts.index', ['status' => 'active', 'search' => 'demo']))
        ->assertSuccessful()
        ->assertSee('data-active-filters', false);
});

it('accounts index prefers a single view action in each row', function () {
    $admin = createAdmin();
    Account::factory()->create();

    actingAs($admin)
        ->get(route('accounts.index'))
        ->assertSuccessful()
        ->assertSee('data-atelier-table-stage', false)
        ->assertSee('data-atelier-row-density="scan"', false)
        ->assertSee('table-action table-action--primary', false)
        ->assertSee('aria-label="Account row actions"', false)
        ->assertSee('class="flex justify-end" aria-label="Account row actions"', false)
        ->assertDontSee('class="table-actions" aria-label="Account row actions"', false)
        ->assertDontSee('table-action table-action--danger', false)
        ->assertDontSee('onsubmit="return confirm', false);
});

it('accounts index uses the tightened scan hierarchy columns and account cell markers', function () {
    $admin = createAdmin();
    Account::factory()->create();

    actingAs($admin)
        ->get(route('accounts.index'))
        ->assertSuccessful()
        ->assertSeeInOrder([
            'Account',
            'Access',
            'Usage',
            'Last Login',
        ])
        ->assertDontSee('>Email<', false)
        ->assertDontSee('>Status<', false)
        ->assertDontSee('>Privilege<', false)
        ->assertDontSee('>Licenses<', false)
        ->assertDontSee('>Devices<', false)
        ->assertSee('data-account-cell="identity"', false)
        ->assertSee('data-account-cell="access"', false)
        ->assertSee('data-account-cell="usage"', false)
        ->assertSee('aria-label="Actions"', false);
});

it('accounts index preserves long username and email access through truncation titles', function () {
    $admin = createAdmin();

    $account = Account::factory()->create([
        'username' => 'very-long-account-name-for-density-audit-operator',
        'email' => 'very.long.account.name@example.test',
    ]);

    actingAs($admin)
        ->get(route('accounts.index', ['search' => 'very.long.account.name@example.test']))
        ->assertSuccessful()
        ->assertSee('title="'.$account->username.'"', false)
        ->assertSee('title="'.$account->email.'"', false)
        ->assertSee('table-truncate table-truncate-md text-sm', false)
        ->assertSee('table-meta table-truncate table-truncate-lg', false)
        ->assertSee('Search: &quot;very.long.account.name@example.test&quot;', false);
});

it('accounts index shows statistics', function () {
    $admin = createAdmin();

    actingAs($admin)
        ->get(route('accounts.index'))
        ->assertViewHas('statistics', fn ($s) => array_key_exists('total', $s)
            && array_key_exists('active', $s)
            && array_key_exists('suspended', $s)
            && array_key_exists('verified', $s)
        );
});

// --- Status Filters ---

it('can filter accounts by active status', function () {
    $admin = createAdmin();

    Account::factory()->active()->create();
    Account::factory()->suspended()->create();

    $response = actingAs($admin)
        ->get(route('accounts.index', ['status' => 'active']));

    $response->assertSuccessful();
    $accounts = $response->viewData('accounts');
    foreach ($accounts->items() as $account) {
        $isActiveByScope = $account->is_suspended === false
            || ($account->is_suspended === true
                && $account->suspended_until !== null
                && $account->suspended_until->isPast());

        expect($isActiveByScope)->toBeTrue();
    }
});

it('can filter accounts by suspended status', function () {
    $admin = createAdmin();

    Account::factory()->create(['is_suspended' => true, 'suspended_until' => null]);
    Account::factory()->create(['is_suspended' => false]);

    $response = actingAs($admin)
        ->get(route('accounts.index', ['status' => 'suspended']));

    $response->assertSuccessful();
    $accounts = $response->viewData('accounts');
    foreach ($accounts->items() as $account) {
        expect($account->is_suspended)->toBeTrue();
    }
});

it('can filter accounts by verified status', function () {
    $admin = createAdmin();

    Account::factory()->verified()->create();
    Account::factory()->create(['email_verified_at' => null]);

    $response = actingAs($admin)
        ->get(route('accounts.index', ['status' => 'verified']));

    $response->assertSuccessful();
    $accounts = $response->viewData('accounts');
    foreach ($accounts->items() as $account) {
        expect($account->email_verified_at)->not->toBeNull();
    }
});

it('can filter accounts by unverified status', function () {
    $admin = createAdmin();

    Account::factory()->create(['email_verified_at' => null]);
    Account::factory()->verified()->create();

    $response = actingAs($admin)
        ->get(route('accounts.index', ['status' => 'unverified']));

    $response->assertSuccessful();
    $accounts = $response->viewData('accounts');
    foreach ($accounts->items() as $account) {
        expect($account->email_verified_at)->toBeNull();
    }
});

it('can filter accounts by two factor enabled status', function () {
    $admin = createAdmin();

    $withTwoFactor = Account::factory()->withTwoFactor()->create();
    $withoutTwoFactor = Account::factory()->create([
        'two_factor_secret' => null,
        'two_factor_confirmed_at' => null,
    ]);

    $response = actingAs($admin)
        ->get(route('accounts.index', ['status' => '2fa-enabled']));

    $response->assertSuccessful();
    $accounts = $response->viewData('accounts');
    $ids = collect($accounts->items())->pluck('id');

    expect($ids->contains($withTwoFactor->id))->toBeTrue();
    expect($ids->contains($withoutTwoFactor->id))->toBeFalse();
});

// --- License Count Filters ---

it('can filter accounts with no licenses', function () {
    $admin = createAdmin();

    $accountWithNoLicense = Account::factory()->create();
    $accountWithLicense = Account::factory()->create();
    License::factory()->create(['used_by' => $accountWithLicense->id]);

    $response = actingAs($admin)
        ->get(route('accounts.index', ['license_count' => 'none']));

    $response->assertSuccessful();
    $accounts = $response->viewData('accounts');
    $ids = collect($accounts->items())->pluck('id');
    expect($ids->contains($accountWithNoLicense->id))->toBeTrue();
    expect($ids->contains($accountWithLicense->id))->toBeFalse();
});

it('can filter accounts that have licenses', function () {
    $admin = createAdmin();

    $accountWithNoLicense = Account::factory()->create();
    $accountWithLicense = Account::factory()->create();
    License::factory()->create(['used_by' => $accountWithLicense->id]);

    $response = actingAs($admin)
        ->get(route('accounts.index', ['license_count' => 'has']));

    $response->assertSuccessful();
    $accounts = $response->viewData('accounts');
    $ids = collect($accounts->items())->pluck('id');
    expect($ids->contains($accountWithLicense->id))->toBeTrue();
    expect($ids->contains($accountWithNoLicense->id))->toBeFalse();
});

// --- Privilege Filter ---

it('can filter accounts by privilege level', function () {
    $admin = createAdmin();

    $standardUser = Account::factory()->create();
    License::factory()->create([
        'used_by' => $standardUser->id,
        'status' => LicenseStatus::ACTIVE->value,
        'privilege' => LicensePrivilege::STANDARD->value,
        'expires_at' => now()->addYear(),
    ]);

    $ultimateUser = Account::factory()->create();
    License::factory()->create([
        'used_by' => $ultimateUser->id,
        'status' => LicenseStatus::ACTIVE->value,
        'privilege' => LicensePrivilege::ULTIMATE->value,
        'expires_at' => now()->addYear(),
    ]);

    $response = actingAs($admin)
        ->get(route('accounts.index', ['privilege' => LicensePrivilege::STANDARD->value]));

    $response->assertSuccessful();
    $accounts = $response->viewData('accounts');
    $ids = collect($accounts->items())->pluck('id');
    expect($ids->contains($standardUser->id))->toBeTrue();
    expect($ids->contains($ultimateUser->id))->toBeFalse();
});

it('privilege filter excludes expired and inactive licenses', function () {
    $admin = createAdmin();

    $expiredUser = Account::factory()->create();
    License::factory()->create([
        'used_by' => $expiredUser->id,
        'status' => LicenseStatus::ACTIVE->value,
        'privilege' => LicensePrivilege::STANDARD->value,
        'expires_at' => now()->subDay(),
    ]);

    $inactiveUser = Account::factory()->create();
    License::factory()->create([
        'used_by' => $inactiveUser->id,
        'status' => LicenseStatus::SUSPENDED->value,
        'privilege' => LicensePrivilege::STANDARD->value,
        'expires_at' => now()->addYear(),
    ]);

    $activeUser = Account::factory()->create();
    License::factory()->create([
        'used_by' => $activeUser->id,
        'status' => LicenseStatus::ACTIVE->value,
        'privilege' => LicensePrivilege::STANDARD->value,
        'expires_at' => now()->addYear(),
    ]);

    $response = actingAs($admin)
        ->get(route('accounts.index', ['privilege' => LicensePrivilege::STANDARD->value]));

    $response->assertSuccessful();
    $ids = collect($response->viewData('accounts')->items())->pluck('id');

    expect($ids->contains($activeUser->id))->toBeTrue();
    expect($ids->contains($expiredUser->id))->toBeFalse();
    expect($ids->contains($inactiveUser->id))->toBeFalse();
});

// --- Search ---

it('can search accounts by username', function () {
    $admin = createAdmin();

    Account::factory()->create(['username' => 'uniqueusername123']);
    Account::factory()->create(['username' => 'otheraccount456']);

    $response = actingAs($admin)
        ->get(route('accounts.index', ['search' => 'uniqueusername123']));

    $response->assertSuccessful();
    $accounts = $response->viewData('accounts');
    expect($accounts->total())->toBe(1);
    expect($accounts->first()->username)->toBe('uniqueusername123');
});

it('can search accounts by email', function () {
    $admin = createAdmin();

    Account::factory()->create(['email' => 'findme@example.com']);
    Account::factory()->create(['email' => 'other@example.com']);

    $response = actingAs($admin)
        ->get(route('accounts.index', ['search' => 'findme@example.com']));

    $response->assertSuccessful();
    $accounts = $response->viewData('accounts');
    expect($accounts->total())->toBe(1);
});

it('can search accounts by license key', function () {
    $admin = createAdmin();

    $account = Account::factory()->create();
    License::factory()->create([
        'key' => 'SRCH1-12345-ABCDE-FGHIJ-KLMNO',
        'used_by' => $account->id,
    ]);

    $response = actingAs($admin)
        ->get(route('accounts.index', ['search' => 'SRCH1']));

    $response->assertSuccessful();
    $accounts = $response->viewData('accounts');
    $ids = collect($accounts->items())->pluck('id');
    expect($ids->contains($account->id))->toBeTrue();
});

// --- Sort ---

it('defaults to created_at descending sort', function () {
    $admin = createAdmin();

    Account::factory()->create(['created_at' => now()->subDays(5)]);
    Account::factory()->create(['created_at' => now()->subDay()]);

    $response = actingAs($admin)
        ->get(route('accounts.index'));

    $response->assertSuccessful();
    $accounts = $response->viewData('accounts');
    $items = $accounts->items();

    expect($items)->not->toBeEmpty();

    for ($i = 1; $i < count($items); $i++) {
        $previous = $items[$i - 1];
        $current = $items[$i];

        expect($previous->created_at->gt($current->created_at)
            || ($previous->created_at->eq($current->created_at) && $previous->id >= $current->id)
        )->toBeTrue();
    }
});

it('invalid filter and sort inputs are handled safely with default sort fallback', function () {
    $admin = createAdmin();

    Account::factory()->create(['created_at' => now()->subDays(2)]);
    Account::factory()->create(['created_at' => now()->subDay()]);

    $response = actingAs($admin)
        ->get(route('accounts.index', [
            'status' => 'unknown-status',
            'license_count' => 'weird',
            'sort' => 'drop_table',
            'privilege' => 'not-an-int',
        ]));

    $response->assertSuccessful();
    $response->assertViewHas('currentFilters', fn (array $filters) => $filters['sort'] === 'drop_table'
        && $filters['direction'] === 'desc');
});
