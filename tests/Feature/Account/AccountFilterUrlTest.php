<?php

use App\Models\Account;

use function Pest\Laravel\actingAs;

it('filters accounts by privilege and keeps url clean', function () {
    $adminUser = createAdmin();

    $response = actingAs($adminUser)
        ->get('/accounts?privilege=7');

    $response->assertOk()
        ->assertSee('data-active-filters', false);

    $accounts = $response->viewData('accounts');
    expect($accounts->items())->toHaveCount(1);
    expect($accounts->first()->id)->toBe($adminUser->id);
});

it('removes empty parameters from url after filtering', function () {
    $adminUser = createAdmin();

    Account::factory()->create();
    Account::factory()->create();

    $response = actingAs($adminUser)
        ->get('/accounts?status=&privilege=7&license_count=&sort=created_at_desc&search=');

    $response->assertStatus(200);

    $accounts = $response->viewData('accounts');
    expect($accounts->items())->toHaveCount(1);
    expect($accounts->first()->id)->toBe($adminUser->id);
});

it('defaults to created_at_desc sort when no sort is provided', function () {
    $adminUser = createAdmin();

    Account::factory()->create(['created_at' => now()->subDays(2)]);
    Account::factory()->create(['created_at' => now()->subDay()]);

    $response = actingAs($adminUser)
        ->get('/accounts');

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
