<?php

use App\Enums\LicensePrivilege;
use App\Enums\LicenseStatus;
use App\Models\Account;
use App\Models\License;

beforeEach(function () {
    $this->admin = createAdmin();

    $this->user = Account::factory()->create(['username' => 'testuser']);

    $this->license1 = License::factory()->create([
        'key' => 'TEST1-12345-ABCDE-FGHIJ-KLMNO',
        'status' => LicenseStatus::ACTIVE,
        'privilege' => LicensePrivilege::STANDARD,
        'used_by' => $this->user->id,
        'expires_at' => now()->addYear(),
    ]);

    $this->license2 = License::factory()->create([
        'key' => 'TEST2-67890-VWXYZ-12345-67890',
        'status' => LicenseStatus::SUSPENDED,
        'privilege' => LicensePrivilege::DEFAULT,
        'used_by' => null,
        'expires_at' => now()->addYear(),
    ]);

    $this->license3 = License::factory()->create([
        'key' => 'TEST3-ABCDE-12345-VWXYZ-ABCDE',
        'status' => LicenseStatus::ACTIVE,
        'privilege' => LicensePrivilege::STANDARD,
        'used_by' => null,
        'expires_at' => now()->addYear(),
    ]);
});

it('admin can filter licenses by status', function () {
    $response = $this->actingAs($this->admin)
        ->get('/licenses?status=1');

    $response->assertStatus(200);
    $response->assertSee('TEST1-12345-ABCDE-FGHIJ-KLMNO');
    $response->assertSee('TEST3-ABCDE-12345-VWXYZ-ABCDE');
    $response->assertDontSee('TEST2-67890-VWXYZ-12345-67890');
});

it('admin can filter licenses by privilege', function () {
    $response = $this->actingAs($this->admin)
        ->get('/licenses?privilege=1');

    $response->assertStatus(200);
    $response->assertSee('TEST1-12345-ABCDE-FGHIJ-KLMNO');
    $response->assertSee('TEST3-ABCDE-12345-VWXYZ-ABCDE');
    $response->assertDontSee('TEST2-67890-VWXYZ-12345-67890');
});

it('admin can search licenses by key', function () {
    $response = $this->actingAs($this->admin)
        ->get('/licenses?search=TEST1');

    $response->assertStatus(200);
    $response->assertSee('TEST1-12345-ABCDE-FGHIJ-KLMNO');
    $response->assertDontSee('TEST2-67890-VWXYZ-12345-67890');
    $response->assertDontSee('TEST3-ABCDE-12345-VWXYZ-ABCDE');
});

it('admin can search licenses by username', function () {
    $response = $this->actingAs($this->admin)
        ->get('/licenses?search=testuser');

    $response->assertStatus(200);
    $response->assertSee('TEST1-12345-ABCDE-FGHIJ-KLMNO');
    $response->assertDontSee('TEST2-67890-VWXYZ-12345-67890');
    $response->assertDontSee('TEST3-ABCDE-12345-VWXYZ-ABCDE');
});

it('admin can combine filters', function () {
    $response = $this->actingAs($this->admin)
        ->get('/licenses?status=1&privilege=1');

    $response->assertStatus(200);
    $response->assertSee('TEST1-12345-ABCDE-FGHIJ-KLMNO');
    $response->assertSee('TEST3-ABCDE-12345-VWXYZ-ABCDE');
    $response->assertDontSee('TEST2-67890-VWXYZ-12345-67890');
});

it('regular user cannot see filter section', function () {
    $response = $this->actingAs($this->user)
        ->get('/licenses');

    $response->assertStatus(200);
    $response->assertDontSee('Filter Licenses');
    $response->assertDontSee('All Statuses');
    $response->assertDontSee('All Privileges');
});

it('regular user only sees their own licenses', function () {
    $response = $this->actingAs($this->user)
        ->get('/licenses');

    $response->assertStatus(200);
    $response->assertSee('TEST1-12345-ABCDE-FGHIJ-KLMNO');
    $response->assertDontSee('TEST2-67890-VWXYZ-12345-67890');
    $response->assertDontSee('TEST3-ABCDE-12345-VWXYZ-ABCDE');
});

it('empty filter values maintain all statuses selection', function () {
    $response = $this->actingAs($this->admin)
        ->get('/licenses?status=&privilege=&search=');

    $response->assertStatus(200);

    $response->assertSee('TEST1-12345-ABCDE-FGHIJ-KLMNO');
    $response->assertSee('TEST2-67890-VWXYZ-12345-67890');
    $response->assertSee('TEST3-ABCDE-12345-VWXYZ-ABCDE');

    $response->assertSee('All Statuses');
    $response->assertSee('All Privileges');

    $response->assertDontSee('Active filters:');
});

it('single filter only includes non-empty parameters', function () {
    $response = $this->actingAs($this->admin)
        ->get('/licenses?privilege=1');

    $response->assertStatus(200);

    $response->assertSee('TEST1-12345-ABCDE-FGHIJ-KLMNO');
    $response->assertSee('TEST3-ABCDE-12345-VWXYZ-ABCDE');
    $response->assertDontSee('TEST2-67890-VWXYZ-12345-67890');

    $response->assertSee('Active filters:');
    $response->assertSee('Privilege:');
    $response->assertDontSee('Status:');
    $response->assertDontSee('Search:');
});
