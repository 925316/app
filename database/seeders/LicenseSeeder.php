<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\License;
use App\Enums\LicenseStatus;
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
        // Test Case 1: Basic license (privilege 1) - UNUSED
        License::create([
            'key' => 'BASIC-12345-ABCDE-FGHIJ-KLMNO',
            'type' => 1, // base
            'privilege' => 1, // basic
            'status' => 0, // unused
            'expires_at' => now()->addYear(),
            'notes' => 'Test: Basic license for activation',
        ]);

        // Test Case 2: Regular license (privilege 2) - UNUSED
        License::create([
            'key' => 'REGUL-67890-BCDEF-GHIJK-LMNOP',
            'type' => 1, // base
            'privilege' => 2, // regular
            'status' => 0, // unused
            'expires_at' => now()->addYear(),
            'notes' => 'Test: Regular license for activation',
        ]);

        // Test Case 3: Ultimate license (privilege 3) - UNUSED
        License::create([
            'key' => 'ULTIM-13579-CDEFG-HIJKL-MNOPQ',
            'type' => 1, // base
            'privilege' => 3, // ultimate
            'status' => 0, // unused
            'expires_at' => now()->addYear(),
            'notes' => 'Test: Ultimate license for activation',
        ]);

        // Test Case 4: Admin/Staff license (privilege 5) - UNUSED
        License::create([
            'key' => 'STAFF-24680-DEFGH-IJKLM-NOPQR',
            'type' => 1, // base
            'privilege' => 5, // staff
            'status' => 0, // unused
            'expires_at' => now()->addYear(),
            'notes' => 'Test: Staff license for activation',
        ]);

        // Test Case 5: Upgrade license (type 2, privilege 3) - UNUSED
        License::create([
            'key' => 'UPGRA-11223-34567-89ABC-DEFGH',
            'type' => 2, // upgrade
            'privilege' => 3, // ultimate
            'status' => 0, // unused
            'expires_at' => now()->addYear(),
            'notes' => 'Test: Upgrade license for activation',
        ]);

        // Test Case 6: Expired license - UNUSED
        License::create([
            'key' => 'EXPIR-44556-67890-ABCDE-FGHIJ',
            'type' => 1, // base
            'privilege' => 1, // basic
            'status' => 0, // unused
            'expires_at' => now()->subDay(), // expired
            'notes' => 'Test: Expired license (cannot activate)',
        ]);

        // Test Case 7: Revoked license
        License::create([
            'key' => 'REVOK-77889-90123-45678-9ABCD',
            'type' => 1, // base
            'privilege' => 1, // basic
            'status' => 5, // revoked
            'expires_at' => now()->addYear(),
            'notes' => 'Test: Revoked license (cannot activate)',
        ]);

        // Test Case 8: Suspended license
        License::create([
            'key' => 'SUSPE-12345-67890-ABCDE-FGHIJ',
            'type' => 1, // base
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
                'type' => 1, // base
                'privilege' => 2, // regular
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
            ->base()
            ->privilege(1) // basic
            ->create();

        License::factory()
            ->count(10)
            ->unused()
            ->base()
            ->privilege(2) // regular
            ->create();

        License::factory()
            ->count(10)
            ->unused()
            ->base()
            ->privilege(3) // ultimate
            ->create();

        // Create active licenses assigned to accounts
        License::factory()
            ->count(10)
            ->active()
            ->state(['used_by' => fn() => $accounts[array_rand($accounts)]])
            ->base()
            ->privilege(2) // regular
            ->create();

        License::factory()
            ->count(10)
            ->active()
            ->state(['used_by' => fn() => $accounts[array_rand($accounts)]])
            ->base()
            ->privilege(3) // ultimate
            ->create();

        // Create special privilege licenses
        License::factory()
            ->count(2)
            ->active()
            ->state(['used_by' => fn() => $accounts[array_rand($accounts)]])
            ->base()
            ->privilege(4) // tester
            ->create();

        License::factory()
            ->count(2)
            ->active()
            ->state(['used_by' => fn() => $accounts[array_rand($accounts)]])
            ->base()
            ->privilege(5) // staff
            ->create();

        // Create suspended licenses
        License::factory()
            ->count(10)
            ->suspended()
            ->state(['used_by' => fn() => $accounts[array_rand($accounts)]])
            ->base()
            ->privilege(2) // regular
            ->create();

        // Create expired licenses
        License::factory()
            ->count(10)
            ->expired()
            ->state(['used_by' => fn() => $accounts[array_rand($accounts)]])
            ->base()
            ->privilege(2) // regular
            ->create();

        // Create upgrade licenses
        License::factory()
            ->count(10)
            ->unused()
            ->upgrade()
            ->privilege(3) // ultimate
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
            ['BASIC-12345-ABCDE-FGHIJ-KLMNO', 'Basic (1)', 'Can activate'],
            ['REGUL-67890-BCDEF-GHIJK-LMNOP', 'Regular (2)', 'Can activate'],
            ['ULTIM-13579-CDEFG-HIJKL-MNOPQ', 'Ultimate (3)', 'Can activate'],
            ['STAFF-24680-DEFGH-IJKLM-NOPQR', 'Staff (5)', 'Can activate (gives admin)'],
            ['UPGRA-11223-34567-89ABC-DEFGH', 'Upgrade (3)', 'Can activate'],
            ['EXPIR-44556-67890-ABCDE-FGHIJ', 'Basic (1)', 'EXPIRED - Cannot activate'],
            ['REVOK-77889-90123-45678-9ABCD', 'Basic (1)', 'REVOKED - Cannot activate'],
            ['SUSPE-12345-67890-ABCDE-FGHIJ', 'Basic (1)', 'SUSPENDED - Cannot activate'],
            ['ACTIV-11111-22222-33333-44444', 'Regular (2)', 'ALREADY ACTIVE - Cannot activate'],
        ];

        $headers = ['License Key', 'Privilege', 'Status'];
        $this->command->table($headers, $testLicenses);

        $this->command->info('');
        $this->command->comment('Testing Scenarios:');
        $this->command->comment('1. Start with no active license → Try activating any UNUSED license');
        $this->command->comment('2. Have Basic → Try upgrading to Regular/Ultimate/Staff');
        $this->command->comment('3. Have Regular → Try upgrading to Ultimate/Staff (should work)');
        $this->command->comment('4. Have Regular → Try downgrading to Basic (should fail)');
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

        $types = [1 => 'base', 2 => 'upgrade'];
        $privileges = [
            0 => 'default',
            1 => 'basic',
            2 => 'regular',
            3 => 'ultimate',
            4 => 'tester',
            5 => 'staff',
        ];

        $headers = ['Status', 'Type', 'Privilege', 'Count'];
        $rows = [];

        $total = 0;

        foreach ($statuses as $statusId => $statusName) {
            foreach ($types as $typeId => $typeName) {
                foreach ($privileges as $privilegeId => $privilegeName) {
                    $count = License::where('status', $statusId)
                        ->where('type', $typeId)
                        ->where('privilege', $privilegeId)
                        ->count();

                    if ($count > 0) {
                        $rows[] = [
                            $statusName,
                            $typeName,
                            $privilegeName,
                            $count,
                        ];
                        $total += $count;
                    }
                }
            }
        }

        $this->command->table($headers, $rows);
        $this->command->info("Total licenses: {$total}");
        $this->command->info(str_repeat('-', 50));
    }
}
