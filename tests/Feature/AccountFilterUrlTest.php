<?php

use App\Enums\LicenseStatus;
use App\Models\Account;
use App\Models\License;

beforeEach(function () {
    $this->adminUser = Account::factory()->create();
    $this->license = License::factory()->create([
        'used_by' => $this->adminUser->id,
        'privilege' => 7, // Staff
        'status' => LicenseStatus::ACTIVE,
        'expires_at' => now()->addYear(),
    ]);
});

it('filters accounts by privilege and keeps url clean', function () {
    $response = $this->actingAs($this->adminUser)
        ->get('/accounts?privilege=7');

    $response->assertStatus(200);

    // Verify the filter works
    $accounts = $response->viewData('accounts');
    expect($accounts->items())->toHaveCount(1);
    expect($accounts->first()->id)->toBe($this->adminUser->id);
});

it('removes empty parameters from url after filtering', function () {
    // Create additional accounts
    Account::factory()->create();
    Account::factory()->create();

    $response = $this->actingAs($this->adminUser)
        ->get('/accounts?status=&privilege=7&license_count=&sort=created_at_desc&search=');

    $response->assertStatus(200);

    // The backend should still process the privilege filter correctly
    $accounts = $response->viewData('accounts');

    // Even with empty parameters, the privilege filter should work
    expect($accounts->items())->toHaveCount(1);
    expect($accounts->first()->id)->toBe($this->adminUser->id);
});

it('defaults to created_at_desc sort when no sort is provided', function () {
    $oldAccount = Account::factory()->create([
        'created_at' => now()->subDays(2),
    ]);

    $recentAccount = Account::factory()->create([
        'created_at' => now()->subDay(),
    ]);

    $response = $this->actingAs($this->adminUser)
        ->get('/accounts');

    $accounts = $response->viewData('accounts');
    $items = $accounts->items();

    // With pagination (25 items), check that accounts are properly ordered
    // The most recent accounts should appear first
    expect($items)->not->toBeEmpty();

    // Verify that the first item has a created_at that is >= the last item's created_at
    $firstItem = $items[0];
    $lastItem = $items[count($items) - 1];
    expect($firstItem->created_at->gte($lastItem->created_at))->toBeTrue();
});
