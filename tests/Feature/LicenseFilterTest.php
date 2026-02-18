<?php

namespace Tests\Feature;

use App\Enums\LicensePrivilege;
use App\Enums\LicenseStatus;
use App\Models\Account;
use App\Models\License;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LicenseFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user with admin privileges
        $this->admin = Account::factory()->create([
            'username' => 'admin',
            'email' => 'admin@example.com',
        ]);

        // Give admin user an active license with staff privilege (admin level)
        License::factory()->create([
            'key' => 'ADMIN-12345-ABCDE-FGHIJ-KLMNO',
            'status' => LicenseStatus::ACTIVE,
            'privilege' => LicensePrivilege::STAFF,
            'used_by' => $this->admin->id,
            'expires_at' => now()->addYear(),
        ]);

        // Create regular user
        $this->user = Account::factory()->create([
            'username' => 'testuser',
            'email' => 'test@example.com',
        ]);

        // Create test licenses
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
    }

    public function test_admin_can_filter_licenses_by_status(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/licenses?status=1');

        $response->assertStatus(200);
        $response->assertSee('TEST1-12345-ABCDE-FGHIJ-KLMNO');
        $response->assertSee('TEST3-ABCDE-12345-VWXYZ-ABCDE');
        $response->assertDontSee('TEST2-67890-VWXYZ-12345-67890');
    }

    public function test_admin_can_filter_licenses_by_privilege(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/licenses?privilege=1');

        $response->assertStatus(200);
        $response->assertSee('TEST1-12345-ABCDE-FGHIJ-KLMNO');
        $response->assertSee('TEST3-ABCDE-12345-VWXYZ-ABCDE');
        $response->assertDontSee('TEST2-67890-VWXYZ-12345-67890');
    }

    public function test_admin_can_search_licenses_by_key(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/licenses?search=TEST1');

        $response->assertStatus(200);
        $response->assertSee('TEST1-12345-ABCDE-FGHIJ-KLMNO');
        $response->assertDontSee('TEST2-67890-VWXYZ-12345-67890');
        $response->assertDontSee('TEST3-ABCDE-12345-VWXYZ-ABCDE');
    }

    public function test_admin_can_search_licenses_by_username(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/licenses?search=testuser');

        $response->assertStatus(200);
        $response->assertSee('TEST1-12345-ABCDE-FGHIJ-KLMNO');
        $response->assertDontSee('TEST2-67890-VWXYZ-12345-67890');
        $response->assertDontSee('TEST3-ABCDE-12345-VWXYZ-ABCDE');
    }

    public function test_admin_can_combine_filters(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/licenses?status=1&privilege=1');

        $response->assertStatus(200);
        $response->assertSee('TEST1-12345-ABCDE-FGHIJ-KLMNO');
        $response->assertSee('TEST3-ABCDE-12345-VWXYZ-ABCDE');
        $response->assertDontSee('TEST2-67890-VWXYZ-12345-67890');
    }

    public function test_regular_user_cannot_see_filter_section(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/licenses');

        $response->assertStatus(200);
        $response->assertDontSee('Filter Licenses');
        $response->assertDontSee('All Statuses');
        $response->assertDontSee('All Privileges');
    }

    public function test_regular_user_only_sees_their_own_licenses(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/licenses');

        $response->assertStatus(200);
        $response->assertSee('TEST1-12345-ABCDE-FGHIJ-KLMNO');
        $response->assertDontSee('TEST2-67890-VWXYZ-12345-67890');
        $response->assertDontSee('TEST3-ABCDE-12345-VWXYZ-ABCDE');
    }

    public function test_empty_filter_values_maintain_all_statuses_selection(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/licenses?status=&privilege=&search=');

        $response->assertStatus(200);

        // Verify that all licenses are shown when filters are empty
        $response->assertSee('TEST1-12345-ABCDE-FGHIJ-KLMNO');
        $response->assertSee('TEST2-67890-VWXYZ-12345-67890');
        $response->assertSee('TEST3-ABCDE-12345-VWXYZ-ABCDE');

        // Verify that the "All Statuses" and "All Privileges" options are present
        $response->assertSee('All Statuses');
        $response->assertSee('All Privileges');

        // Verify that no active filters badge is shown when filters are empty
        // Use assertDontSee with the full phrase to avoid false positives from other "Active" text
        $response->assertDontSee('Active filters:');
    }

    public function test_single_filter_only_includes_non_empty_parameters(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/licenses?privilege=1');

        $response->assertStatus(200);

        // Verify that only licenses with privilege=1 are shown
        $response->assertSee('TEST1-12345-ABCDE-FGHIJ-KLMNO');
        $response->assertSee('TEST3-ABCDE-12345-VWXYZ-ABCDE');
        $response->assertDontSee('TEST2-67890-VWXYZ-12345-67890');

        // Verify that the active filters badge shows only the privilege filter
        // Using assertSee with the full phrase to match the badge text
        $response->assertSee('Active filters:');
        $response->assertSee('Privilege:');
        $response->assertDontSee('Status:');
        $response->assertDontSee('Search:');
    }
}
