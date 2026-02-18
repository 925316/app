<?php

use App\Enums\LicensePrivilege;
use App\Enums\LicenseStatus;
use App\Models\Account;
use App\Models\License;

beforeEach(function () {
    $this->admin = createAdmin();
});

// --- Basic Access ---

it('admin can view accounts index', function () {
    $this->actingAs($this->admin)
        ->get(route('accounts.index'))
        ->assertSuccessful()
        ->assertViewIs('accounts.index')
        ->assertViewHasAll(['accounts', 'statistics', 'statusOptions', 'privilegeOptions', 'currentFilters']);
});

it('accounts index shows statistics', function () {
    $this->actingAs($this->admin)
        ->get(route('accounts.index'))
        ->assertViewHas('statistics', fn ($s) => array_key_exists('total', $s)
            && array_key_exists('active', $s)
            && array_key_exists('suspended', $s)
            && array_key_exists('verified', $s)
        );
});

// --- Status Filters ---

it('can filter accounts by active status', function () {
    Account::factory()->create(['is_suspended' => false]);
    Account::factory()->create(['is_suspended' => true]);

    $response = $this->actingAs($this->admin)
        ->get(route('accounts.index', ['status' => 'active']));

    $response->assertSuccessful();
    $accounts = $response->viewData('accounts');
    foreach ($accounts->items() as $account) {
        expect($account->isSuspended())->toBeFalse();
    }
});

it('can filter accounts by suspended status', function () {
    Account::factory()->create(['is_suspended' => true, 'suspended_until' => null]);
    Account::factory()->create(['is_suspended' => false]);

    $response = $this->actingAs($this->admin)
        ->get(route('accounts.index', ['status' => 'suspended']));

    $response->assertSuccessful();
    $accounts = $response->viewData('accounts');
    foreach ($accounts->items() as $account) {
        expect($account->is_suspended)->toBeTrue();
    }
});

it('can filter accounts by verified status', function () {
    Account::factory()->verified()->create();
    Account::factory()->create(['email_verified_at' => null]);

    $response = $this->actingAs($this->admin)
        ->get(route('accounts.index', ['status' => 'verified']));

    $response->assertSuccessful();
    $accounts = $response->viewData('accounts');
    foreach ($accounts->items() as $account) {
        expect($account->email_verified_at)->not->toBeNull();
    }
});

it('can filter accounts by unverified status', function () {
    Account::factory()->create(['email_verified_at' => null]);
    Account::factory()->verified()->create();

    $response = $this->actingAs($this->admin)
        ->get(route('accounts.index', ['status' => 'unverified']));

    $response->assertSuccessful();
    $accounts = $response->viewData('accounts');
    foreach ($accounts->items() as $account) {
        expect($account->email_verified_at)->toBeNull();
    }
});

// --- License Count Filters ---

it('can filter accounts with no licenses', function () {
    $accountWithNoLicense = Account::factory()->create();
    $accountWithLicense = Account::factory()->create();
    License::factory()->create(['used_by' => $accountWithLicense->id]);

    $response = $this->actingAs($this->admin)
        ->get(route('accounts.index', ['license_count' => 'none']));

    $response->assertSuccessful();
    $accounts = $response->viewData('accounts');
    $ids = collect($accounts->items())->pluck('id');
    expect($ids->contains($accountWithNoLicense->id))->toBeTrue();
    expect($ids->contains($accountWithLicense->id))->toBeFalse();
});

it('can filter accounts that have licenses', function () {
    $accountWithNoLicense = Account::factory()->create();
    $accountWithLicense = Account::factory()->create();
    License::factory()->create(['used_by' => $accountWithLicense->id]);

    $response = $this->actingAs($this->admin)
        ->get(route('accounts.index', ['license_count' => 'has']));

    $response->assertSuccessful();
    $accounts = $response->viewData('accounts');
    $ids = collect($accounts->items())->pluck('id');
    expect($ids->contains($accountWithLicense->id))->toBeTrue();
    expect($ids->contains($accountWithNoLicense->id))->toBeFalse();
});

// --- Privilege Filter ---

it('can filter accounts by privilege level', function () {
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

    $response = $this->actingAs($this->admin)
        ->get(route('accounts.index', ['privilege' => LicensePrivilege::STANDARD->value]));

    $response->assertSuccessful();
    $accounts = $response->viewData('accounts');
    $ids = collect($accounts->items())->pluck('id');
    expect($ids->contains($standardUser->id))->toBeTrue();
    expect($ids->contains($ultimateUser->id))->toBeFalse();
});

// --- Search ---

it('can search accounts by username', function () {
    Account::factory()->create(['username' => 'uniqueusername123']);
    Account::factory()->create(['username' => 'otheraccount456']);

    $response = $this->actingAs($this->admin)
        ->get(route('accounts.index', ['search' => 'uniqueusername123']));

    $response->assertSuccessful();
    $accounts = $response->viewData('accounts');
    expect($accounts->total())->toBe(1);
    expect($accounts->first()->username)->toBe('uniqueusername123');
});

it('can search accounts by email', function () {
    Account::factory()->create(['email' => 'findme@example.com']);
    Account::factory()->create(['email' => 'other@example.com']);

    $response = $this->actingAs($this->admin)
        ->get(route('accounts.index', ['search' => 'findme@example.com']));

    $response->assertSuccessful();
    $accounts = $response->viewData('accounts');
    expect($accounts->total())->toBe(1);
});

it('can search accounts by license key', function () {
    $account = Account::factory()->create();
    License::factory()->create([
        'key' => 'SRCH1-12345-ABCDE-FGHIJ-KLMNO',
        'used_by' => $account->id,
    ]);

    $response = $this->actingAs($this->admin)
        ->get(route('accounts.index', ['search' => 'SRCH1']));

    $response->assertSuccessful();
    $accounts = $response->viewData('accounts');
    $ids = collect($accounts->items())->pluck('id');
    expect($ids->contains($account->id))->toBeTrue();
});

// --- Sort ---

it('defaults to created_at descending sort', function () {
    Account::factory()->create(['created_at' => now()->subDays(5)]);
    Account::factory()->create(['created_at' => now()->subDay()]);

    $response = $this->actingAs($this->admin)
        ->get(route('accounts.index'));

    $response->assertSuccessful();
    $accounts = $response->viewData('accounts');
    $items = $accounts->items();

    expect($items[0]->created_at->gte($items[count($items) - 1]->created_at))->toBeTrue();
});
