<?php

use App\Models\Account;
use App\Models\AccountDevice;

use function Pest\Laravel\actingAs;

it('renders copy-friendly truncated hwid affordances on the user device history page', function () {
    $user = createUserWithLicense(1);
    $hwidHash = str_repeat('a', 64);

    AccountDevice::factory()->create([
        'account_id' => $user->id,
        'hwid_hash' => $hwidHash,
        'bound_at' => now(),
        'unbound_at' => null,
    ]);

    actingAs($user)
        ->get(route('devices.index'))
        ->assertSuccessful()
        ->assertSee('data-device-hwid-copy="true"', false)
        ->assertSee('data-copy-value="'.$hwidHash.'"', false)
        ->assertSee('title="'.$hwidHash.'"', false)
        ->assertSee('Device', false)
        ->assertSee('Activity', false);
});

it('renders admin device rows with export access and copy-friendly hwid affordances', function () {
    $admin = createAdmin();
    $deviceOwner = Account::factory()->create();
    $hwidHash = str_repeat('f', 64);

    AccountDevice::factory()->create([
        'account_id' => $deviceOwner->id,
        'hwid_hash' => $hwidHash,
        'bound_at' => now(),
        'unbound_at' => null,
        'ip_address' => '203.0.113.10',
        'country_code' => 'US',
    ]);

    actingAs($admin)
        ->get(route('devices.index'))
        ->assertSuccessful()
        ->assertSee(route('devices.export'), false)
        ->assertSee('data-device-hwid-copy="true"', false)
        ->assertSee('data-copy-value="'.$hwidHash.'"', false)
        ->assertSee('title="'.$hwidHash.'"', false)
        ->assertSee('Device', false)
        ->assertSee('State', false);
});
