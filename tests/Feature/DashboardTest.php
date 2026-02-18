<?php

use App\Models\Account;

it('unauthenticated user is redirected to login from dashboard', function () {
    $this->get(route('dashboard'))
        ->assertRedirect(route('login'));
});

it('admin can access dashboard', function () {
    $admin = createAdmin();

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertSuccessful();
});

it('user with license can access dashboard', function () {
    $user = createUserWithLicense(1);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful();
});

it('user without license can access dashboard', function () {
    $user = Account::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful();
});

it('admin sees the admin panel view', function () {
    $admin = createAdmin();

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertViewIs('dashboard.admin-panel');
});

it('regular user sees the user panel view', function () {
    $user = createUserWithLicense(1);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertViewIs('dashboard.user-panel');
});
