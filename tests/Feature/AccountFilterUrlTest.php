<?php

use App\Models\Account;

beforeEach(function () {
    $this->adminUser = createAdmin();
});

it('filters accounts by privilege and keeps url clean', function () {
    $response = $this->actingAs($this->adminUser)
        ->get('/accounts?privilege=7');

    $response->assertStatus(200);

    $accounts = $response->viewData('accounts');
    expect($accounts->items())->toHaveCount(1);
    expect($accounts->first()->id)->toBe($this->adminUser->id);
});

it('removes empty parameters from url after filtering', function () {
    Account::factory()->create();
    Account::factory()->create();

    $response = $this->actingAs($this->adminUser)
        ->get('/accounts?status=&privilege=7&license_count=&sort=created_at_desc&search=');

    $response->assertStatus(200);

    $accounts = $response->viewData('accounts');
    expect($accounts->items())->toHaveCount(1);
    expect($accounts->first()->id)->toBe($this->adminUser->id);
});

it('defaults to created_at_desc sort when no sort is provided', function () {
    Account::factory()->create(['created_at' => now()->subDays(2)]);
    Account::factory()->create(['created_at' => now()->subDay()]);

    $response = $this->actingAs($this->adminUser)
        ->get('/accounts');

    $accounts = $response->viewData('accounts');
    $items = $accounts->items();

    expect($items)->not->toBeEmpty();

    $firstItem = $items[0];
    $lastItem = $items[count($items) - 1];
    expect($firstItem->created_at->gte($lastItem->created_at))->toBeTrue();
});
