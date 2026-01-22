<?php

namespace Database\Seeders;

use App\Enums\LicenseStatus;
use App\Models\Account;
use App\Models\License;
use Illuminate\Database\Seeder;

class LicenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createSpecificTestLicenses();
        $this->createBulkDemoData();
        $this->createLicensesForAccounts();
        $this->displayTestLicenseKeys();
        $this->displayLicenseStats();
    }

    /**
     * Create specific licenses for testing all scenarios.
     * These licenses follow the strict format: XXXXX-XXXXX-XXXXX-XXXXX-XXXXX
     * Pattern: '^[A-Z0-9]{5}-[0-9A-F]{5}-[A-Z2-7]{5}-[A-Z3-8]{5}-[A-Z0-9]{5}$'
     */
    private function createSpecificTestLicenses(): void
    {
        // Test Case 1: Standard license (privilege 1) - UNUSED
        License::create([
            'key' => 'STAND-12345-ABCDE-FGHIJ-KLMNO',
            'privilege' => 1, // standard
            'status' => 0, // unused
            'expires_at' => now()->addYear(),
            'notes' => 'Test: Standard license for activation',
        ]);

        // Test Case 2: Upgrade license (privilege 2) - UNUSED
        License::create([
            'key' => 'STD2U-67890-BCDEF-GHIJK-LMNOP',
            'privilege' => 2, // upgrade
            'status' => 0, // unused
            'expires_at' => now()->addYear(),
            'notes' => 'Test: Upgrade license for activation',
        ]);

        // Test Case 3: Ultimate license (privilege 3) - UNUSED
        License::create([
            'key' => 'ULTIM-13579-CDEFG-HIJKL-MNOPQ',
            'privilege' => 3, // ultimate
            'status' => 0, // unused
            'expires_at' => now()->addYear(),
            'notes' => 'Test: Ultimate license for activation',
        ]);

        // Test Case 4: Admin/Staff license (privilege 7) - UNUSED
        License::create([
            'key' => 'STAFF-24680-DEFGH-IJKLM-NOPQR',
            'privilege' => 7, // staff
            'status' => 0, // unused
            'expires_at' => now()->addYear(),
            'notes' => 'Test: Staff license for activation',
        ]);

        // Test Case 5: Upgrade license (privilege 3) - UNUSED
        License::create([
            'key' => 'UPGRA-11223-34567-89ABC-DEFGH',
            'privilege' => 3, // ultimate
            'status' => 0, // unused
            'expires_at' => now()->addYear(),
            'notes' => 'Test: Upgrade license for activation',
        ]);

        // Test Case 6: Expired license - UNUSED
        License::create([
            'key' => 'EXPIR-44556-67890-ABCDE-FGHIJ',
            'privilege' => 1, // basic
            'status' => 0, // unused
            'expires_at' => now()->subDay(), // expired
            'notes' => 'Test: Expired license (cannot activate)',
        ]);

        // Test Case 7: Revoked license
        License::create([
            'key' => 'REVOK-77889-90123-45678-9ABCD',
            'privilege' => 1, // basic
            'status' => 5, // revoked
            'expires_at' => now()->addYear(),
            'notes' => 'Test: Revoked license (cannot activate)',
        ]);

        // Test Case 8: Suspended license
        License::create([
            'key' => 'SUSPE-12345-67890-ABCDE-FGHIJ',
            'privilege' => 1, // basic
            'status' => 2, // suspended
            'expires_at' => now()->addYear(),
            'notes' => 'Test: Suspended license (cannot activate)',
        ]);

        // Test Case 9: Already active license (assigned to a test account)
        $testAccount = Account::first();
        if ($testAccount) {
            License::create([
                'key' => 'ACTIVE-11111-22222-33333-44444',
                'privilege' => 2, // upgrade
                'status' => 1, // active
                'used_by' => $testAccount->id,
                'activated_at' => now(),
                'expires_at' => now()->addYear(),
                'notes' => 'Test: Already active license',
            ]);
        }
    }

    /**
     * Create bulk demo data using factories.
     */
    private function createBulkDemoData(): void
    {
        // Get available accounts for assignment
        $accounts = Account::pluck('id')->toArray();

        // Create unused licenses with different privileges
        License::factory()
            ->count(10)
            ->unused()
            ->standard()
            ->create();

        License::factory()
            ->count(10)
            ->unused()
            ->upgrade()
            ->create();

        License::factory()
            ->count(10)
            ->unused()
            ->ultimate()
            ->create();

        // Create active licenses assigned to accounts
        License::factory()
            ->count(10)
            ->active()
            ->state(['used_by' => fn () => $accounts[array_rand($accounts)]])
            ->upgrade()
            ->create();

        License::factory()
            ->count(10)
            ->active()
            ->state(['used_by' => fn () => $accounts[array_rand($accounts)]])
            ->ultimate()
            ->create();

        // Create special privilege licenses
        License::factory()
            ->count(2)
            ->active()
            ->state(['used_by' => fn () => $accounts[array_rand($accounts)]])
            ->tester()
            ->create();

        License::factory()
            ->count(2)
            ->active()
            ->state(['used_by' => fn () => $accounts[array_rand($accounts)]])
            ->staff()
            ->create();

        // Create suspended licenses
        License::factory()
            ->count(10)
            ->suspended()
            ->state(['used_by' => fn () => $accounts[array_rand($accounts)]])
            ->upgrade()
            ->create();

        // Create expired licenses
        License::factory()
            ->count(10)
            ->expired()
            ->state(['used_by' => fn () => $accounts[array_rand($accounts)]])
            ->upgrade()
            ->create();

    }

    /**
     * Create licenses assigned to specific accounts.
     */
    private function createLicensesForAccounts(): void
    {
        $accounts = Account::take(10)->get();

        foreach ($accounts as $account) {
            // Create one active license per account
            License::factory()
                ->active()
                ->state([
                    'used_by' => $account->id,
                    'activated_at' => fake()->dateTimeBetween('-6 months', '-1 week'),
                ])
                ->create();

            // Create additional non-active licenses
            $otherLicenseCount = random_int(0, 2);

            if ($otherLicenseCount > 0) {
                License::factory()
                    ->count($otherLicenseCount)
                    ->state([
                        'used_by' => $account->id,
                        'activated_at' => fake()->dateTimeBetween('-6 months', '-1 week'),
                    ])
                    ->state(function (array $attributes) {
                        $status = fake()->randomElement([
                            LicenseStatus::UPGRADED->value,
                            LicenseStatus::EXPIRED->value,
                            LicenseStatus::REVOKED->value,
                        ]);

                        return ['status' => $status];
                    })
                    ->create();

                $this->command->info("Account #{$account->id}: {$otherLicenseCount} non-active license(s)");
            }
        }
    }

    /**
     * Display the test license keys for easy reference.
     */
    private function displayTestLicenseKeys(): void
    {
        $this->command->info(str_repeat('=', 60));
        $this->command->info('TEST LICENSE KEYS (For Manual Testing)');
        $this->command->info(str_repeat('=', 60));

        $testLicenses = [
            ['STAND-12345-ABCDE-FGHIJ-KLMNO', 'Standard (1)', 'Can activate'],
            ['STD2U-67890-BCDEF-GHIJK-LMNOP', 'Upgrade (2)', 'Can activate'],
            ['ULTIM-13579-CDEFG-HIJKL-MNOPQ', 'Ultimate (3)', 'Can activate'],
            ['STAFF-24680-DEFGH-IJKLM-NOPQR', 'Staff (7)', 'Can activate (gives admin)'],
            ['UPGRA-11223-34567-89ABC-DEFGH', 'Ultimate (3)', 'Can activate'],
            ['EXPIR-44556-67890-ABCDE-FGHIJ', 'Standard (1)', 'EXPIRED - Cannot activate'],
            ['REVOK-77889-90123-45678-9ABCD', 'Standard (1)', 'REVOKED - Cannot activate'],
            ['SUSPE-12345-67890-ABCDE-FGHIJ', 'Standard (1)', 'SUSPENDED - Cannot activate'],
            ['ACTIV-11111-22222-33333-44444', 'Upgrade (2)', 'ALREADY ACTIVE - Cannot activate'],
        ];

        $headers = ['License Key', 'Privilege', 'Status'];
        $this->command->table($headers, $testLicenses);

        $this->command->info('');
        $this->command->comment('Testing Scenarios:');
        $this->command->comment('1. Start with no active license → Try activating any UNUSED license');
        $this->command->comment('2. Have Standard → Try upgrading to Upgrade/Ultimate/Staff');
        $this->command->comment('3. Have Upgrade → Try upgrading to Ultimate/Staff (should work)');
        $this->command->comment('4. Have Upgrade → Try downgrading to Standard (should fail)');
        $this->command->comment('5. Try activating expired/revoked/suspended licenses (should fail)');
        $this->command->comment('6. Try activating already active licenses (should fail)');
        $this->command->info('');
    }

    /**
     * Display license statistics.
     */
    private function displayLicenseStats(): void
    {
        $this->command->info(str_repeat('-', 50));
        $this->command->info('LICENSE STATISTICS');
        $this->command->info(str_repeat('-', 50));

        $statuses = [
            0 => 'unused',
            1 => 'active',
            2 => 'suspended',
            3 => 'expired',
            4 => 'upgraded',
            5 => 'revoked',
        ];

        $privileges = [
            0 => 'default',
            1 => 'standard',
            2 => 'upgrade',
            3 => 'ultimate',
            6 => 'tester',
            7 => 'staff',
        ];

        $headers = ['Status', 'Privilege', 'Count'];
        $rows = [];

        $total = 0;

        foreach ($statuses as $statusId => $statusName) {
            foreach ($privileges as $privilegeId => $privilegeName) {
                $count = License::where('status', $statusId)
                    ->where('privilege', $privilegeId)
                    ->count();

                if ($count > 0) {
                    $rows[] = [
                        $statusName,
                        $privilegeName,
                        $count,
                    ];
                    $total += $count;
                }
            }
        }

        $this->command->table($headers, $rows);
        $this->command->info("Total licenses: {$total}");
        $this->command->info(str_repeat('-', 50));
    }
}
