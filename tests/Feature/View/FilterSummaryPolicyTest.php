<?php

use App\Enums\LicenseStatus;
use App\Models\Account;
use App\Models\License;

use function Pest\Laravel\actingAs;

it('hides filter summary pills on accounts, licenses, sessions, and device admin pages that already lead with statistics', function () {
    $admin = createAdmin();

    $accountResponse = actingAs($admin)
        ->get(route('accounts.index'));

    $accountResponse
        ->assertSuccessful()
        ->assertDontSee('filter-box-summary', false);

    $licenseOwner = Account::factory()->create(['username' => 'license-owner']);

    License::factory()->active()->create([
        'used_by' => $licenseOwner->id,
        'status' => LicenseStatus::ACTIVE->value,
        'expires_at' => now()->addYear(),
    ]);

    $licenseResponse = actingAs($admin)
        ->get(route('licenses.index', ['status' => '1', 'search' => 'license-owner']));

    $licenseResponse
        ->assertSuccessful()
        ->assertSee('data-active-filters', false)
        ->assertDontSee('filter-box-summary', false);

    $sessionResponse = actingAs($admin)
        ->get(route('sessions.index'));

    $sessionResponse
        ->assertSuccessful()
        ->assertDontSee('filter-box-summary', false);

    $deviceResponse = actingAs($admin)
        ->get(route('devices.index'));

    $deviceResponse
        ->assertSuccessful()
        ->assertDontSee('filter-box-summary', false);
});
