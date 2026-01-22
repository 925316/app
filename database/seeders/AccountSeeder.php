<?php

namespace Database\Seeders;

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
            'key' => 'ADMIN-00000-00000-00000-00000',
            'privilege' => 7, // staff
            'status' => 1, // active
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
            'key' => 'TESTD-00000-00000-00000-00000',
            'privilege' => 6, // tester
            'status' => 1, // active
            'used_by' => $accountWith2FA->id,
            'expires_at' => now()->addYears(10),
            'activated_at' => now(),
            'created_from_ip' => '127.0.0.1',
            'notes' => 'Tester license with full privileges',
        ]);

        // Temporarily suspended account
        $tempSuspended = Account::create([
            'username' => 'suspended_temp',
            'email' => 'suspended_temp@example.com',
            'password' => Hash::make('temp123'),
            'email_verified_at' => now()->subMonths(2),
            'is_suspended' => true,
            'suspension_reason' => 'Multiple Failed Login Attempts',
            'suspended_until' => now()->addDays(7),
        ]);

        // Permanently banned account
        $bannedAccount = Account::create([
            'username' => 'banned_user',
            'email' => 'banned@example.com',
            'password' => Hash::make('banned123'),
            'email_verified_at' => now()->subMonths(4),
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
