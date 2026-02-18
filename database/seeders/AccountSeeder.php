<?php

namespace Database\Seeders;

use App\Enums\LicensePrivilege;
use App\Enums\LicenseStatus;
use App\Models\Account;
use App\Models\License;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createAdminAccount();
        $this->createTestAccounts();
        $this->createSampleAccounts();
        $this->displayAccountStats();
    }

    /**
     * Create the main administrator account.
     */
    private function createAdminAccount(): void
    {
        // Create admin account
        $admin = Account::create([
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'),
            'email_verified_at' => now()->subMonths(6),
            'last_login_at' => now()->subHours(2),
            'last_ip_address' => '10.0.0.1',
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1', 'recovery-code-2'])),
            'two_factor_confirmed_at' => now()->subMonths(3),
        ]);

        // Create administrator license
        License::create([
            'key' => 'ADMIN-ABCDE-ABCDE-ABCDE-ADMIN',
            'privilege' => LicensePrivilege::STAFF->value,
            'status' => LicenseStatus::ACTIVE->value,
            'used_by' => $admin->id,
            'expires_at' => now()->addYears(10),
            'activated_at' => now(),
            'created_from_ip' => '127.0.0.1',
            'notes' => 'Administrator license with full privileges',
        ]);
    }

    /**
     * Create specific test accounts for development.
     */
    private function createTestAccounts(): void
    {
        // Test account with 2FA
        $accountWith2FA = Account::create([
            'username' => 'tester',
            'email' => 'tester@example.com',
            'password' => Hash::make('tester123'),
            'email_verified_at' => now()->subMonths(6),
            'last_login_at' => now()->subDays(1),
            'last_ip_address' => '172.16.0.100',
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['test-code-1', 'test-code-2'])),
            'two_factor_confirmed_at' => now()->subMonths(3),
        ]);

        License::create([
            'key' => 'TESTD-ABCDE-ABCDE-ABCDE-TESTD',
            'privilege' => LicensePrivilege::TESTER->value,
            'status' => LicenseStatus::ACTIVE->value,
            'used_by' => $accountWith2FA->id,
            'expires_at' => now()->addYears(10),
            'activated_at' => now(),
            'created_from_ip' => '127.0.0.1',
            'notes' => 'Tester license with full privileges',
        ]);

        // Temporarily suspended account (multiple failed login attempts)
        Account::create([
            'username' => 'carlos_m',
            'email' => 'carlos.m@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now()->subMonths(2),
            'last_login_at' => now()->subDays(3),
            'last_ip_address' => '203.0.113.45',
            'is_suspended' => true,
            'suspension_reason' => 'Multiple Failed Login Attempts',
            'suspended_until' => now()->addDays(7),
        ]);

        // Permanently banned account (ToS violation)
        Account::create([
            'username' => 'tommy_g',
            'email' => 'tommy.g@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now()->subMonths(4),
            'last_login_at' => now()->subMonths(1),
            'last_ip_address' => '198.51.100.22',
            'is_suspended' => true,
            'suspension_reason' => 'Violation of Terms of Service',
            'suspended_until' => null,
        ]);
    }

    /**
     * Create sample accounts for testing.
     */
    private function createSampleAccounts(): void
    {
        // Create verified accounts
        Account::factory()->count(5)->verified()->create();

        // Create accounts with 2FA enabled
        Account::factory()->count(3)->withTwoFactor()->verified()->create();

        // Create recently active accounts
        Account::factory()->count(4)->recentlyActive()->verified()->create();

        // Create accounts with HWID resets
        Account::factory()->count(3)->withHwidResets()->create();

        // Create unverified accounts
        Account::factory()->count(3)->unverified()->create();

        // Create additional sample accounts
        Account::factory()->count(20)->create();
    }

    /**
     * Display account statistics.
     */
    private function displayAccountStats(): void
    {
        $this->command->info(str_repeat('-', 50));
        $this->command->info('ACCOUNT STATISTICS');
        $this->command->info(str_repeat('-', 50));

        $total = Account::count();
        $verified = Account::whereNotNull('email_verified_at')->count();
        $suspended = Account::where('is_suspended', true)->count();
        $with2fa = Account::whereNotNull('two_factor_secret')->count();
        $withHwidResets = Account::where('hwid_reset_count', '>', 0)->count();

        $this->command->info("Total accounts: {$total}");
        $this->command->info("Verified accounts: {$verified}");
        $this->command->info("Suspended accounts: {$suspended}");
        $this->command->info("Accounts with 2FA: {$with2fa}");
        $this->command->info("Accounts with HWID resets: {$withHwidResets}");
        $this->command->info(str_repeat('-', 50));
    }
}
