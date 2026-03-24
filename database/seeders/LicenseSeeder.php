<?php

namespace Database\Seeders;

use App\Enums\LicensePrivilege;
use App\Enums\LicenseStatus;
use App\Models\Account;
use App\Models\License;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class LicenseSeeder extends Seeder
{
    private const WINDOW_DAYS = 365;

    private const INVENTORY_MULTIPLIER = 2;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createSpecificTestLicenses();
        $this->createUnusedLicensePool();
        $this->createLicensesForAccounts();
        $this->createUpgradeChains();
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
        $now = now();

        // Test Case 1: Standard license - UNUSED
        License::create([
            'key' => 'STAND-12345-ABCDE-FGHIJ-KLMNO',
            'privilege' => LicensePrivilege::STANDARD->value,
            'status' => LicenseStatus::UNUSED->value,
            'expires_at' => $now->copy()->addDays(365),
            'notes' => 'Test: Standard license for activation',
        ]);

        // Test Case 2: Upgrade token - UNUSED (cannot be activated directly)
        License::create([
            'key' => 'STD2U-67890-BCDEF-GHIJK-LMNOP',
            'privilege' => LicensePrivilege::UPGRADE->value,
            'status' => LicenseStatus::UNUSED->value,
            'expires_at' => $now->copy()->addDays(365),
            'notes' => 'Test: Upgrade token (requires existing active base license)',
        ]);

        // Test Case 3: Ultimate license - UNUSED
        License::create([
            'key' => 'ULTIM-13579-CDEFG-HIJKL-MNOPQ',
            'privilege' => LicensePrivilege::ULTIMATE->value,
            'status' => LicenseStatus::UNUSED->value,
            'expires_at' => $now->copy()->addDays(365),
            'notes' => 'Test: Ultimate license for activation',
        ]);

        // Test Case 4: Staff license - UNUSED
        License::create([
            'key' => 'STAFF-24680-DEFGH-IJKLM-NOPQR',
            'privilege' => LicensePrivilege::STAFF->value,
            'status' => LicenseStatus::UNUSED->value,
            'expires_at' => $now->copy()->addDays(365),
            'notes' => 'Test: Staff license for activation',
        ]);

        // Test Case 5: Ultimate license - UNUSED
        License::create([
            'key' => 'UPGRA-11223-34567-38ABC-DEFGH',
            'privilege' => LicensePrivilege::ULTIMATE->value,
            'status' => LicenseStatus::UNUSED->value,
            'expires_at' => $now->copy()->addDays(365),
            'notes' => 'Test: Upgrade license for activation',
        ]);

        // Test Case 6: Expired license - UNUSED
        License::create([
            'key' => 'EXPIR-44556-67234-ABCDE-FGHIJ',
            'privilege' => LicensePrivilege::STANDARD->value,
            'status' => LicenseStatus::UNUSED->value,
            'expires_at' => $now->copy()->subDay(),
            'notes' => 'Test: Expired license (cannot activate)',
        ]);

        // Test Case 7: Revoked license
        License::create([
            'key' => 'REVOK-77889-90123-ABCDE-RABCD',
            'privilege' => LicensePrivilege::STANDARD->value,
            'status' => LicenseStatus::REVOKED->value,
            'activated_at' => $now->copy()->subDays(120),
            'expires_at' => $now->copy()->addDays(245),
            'notes' => 'Test: Revoked license (cannot activate)',
        ]);

        // Test Case 8: Suspended license
        License::create([
            'key' => 'SUSPE-12345-23456-ABCDE-FGHIJ',
            'privilege' => LicensePrivilege::STANDARD->value,
            'status' => LicenseStatus::SUSPENDED->value,
            'activated_at' => $now->copy()->subDays(90),
            'suspended_at' => $now->copy()->subDays(15),
            'expires_at' => $now->copy()->addDays(270),
            'notes' => 'Test: Suspended license (cannot activate)',
        ]);

        // Test Case 9: Already active standard license (assigned to a test account)
        $testAccount = Account::first();
        if ($testAccount) {
            $activeCreatedAt = $now->copy()->subDays(20);
            License::create([
                'key' => 'ACTIV-11111-22222-33333-44444',
                'privilege' => LicensePrivilege::STANDARD->value,
                'status' => LicenseStatus::ACTIVE->value,
                'used_by' => $testAccount->id,
                'created_at' => $activeCreatedAt,
                'activated_at' => $now->copy()->subDays(12),
                'expires_at' => $now->copy()->addDays(353),
                'notes' => 'Test: Already active standard license',
            ]);
        }
    }

    /**
     * Create a pool of currently unused license keys.
     */
    private function createUnusedLicensePool(): void
    {
        License::factory()
            ->count(18 * self::INVENTORY_MULTIPLIER)
            ->unused()
            ->standard()
            ->create();

        License::factory()
            ->count(10 * self::INVENTORY_MULTIPLIER)
            ->unused()
            ->upgrade()
            ->create();

        License::factory()
            ->count(12 * self::INVENTORY_MULTIPLIER)
            ->unused()
            ->ultimate()
            ->create();

        License::factory()
            ->count(6 * self::INVENTORY_MULTIPLIER)
            ->unused()
            ->staff()
            ->create();

        $this->command->info('Scaled unused license inventory by x'.self::INVENTORY_MULTIPLIER);
    }

    /**
     * Create licenses assigned to specific accounts.
     * Only assigns to accounts created by factories (not test accounts with @test.com emails).
     */
    private function createLicensesForAccounts(): void
    {
        $accounts = Account::query()
            ->where('email', 'not like', '%@test.com')
            ->take(20)
            ->get();

        foreach ($accounts as $account) {
            $activeLicense = License::query()
                ->where('used_by', $account->id)
                ->where('status', LicenseStatus::ACTIVE->value)
                ->first();

            if (! $activeLicense) {
                $activatedAt = $this->pickActivationTime($account->created_at);
                $activePrivilege = fake()->randomElement([
                    LicensePrivilege::STANDARD,
                    LicensePrivilege::ULTIMATE,
                    LicensePrivilege::TESTER,
                    LicensePrivilege::STAFF,
                ]);

                $activeLicense = License::factory()
                    ->active()
                    ->privilege($activePrivilege->value)
                    ->state([
                        'used_by' => $account->id,
                        'created_at' => $activatedAt->copy()->subDays(fake()->numberBetween(3, 21)),
                        'activated_at' => $activatedAt,
                        'expires_at' => $activatedAt->copy()->addDays(fake()->numberBetween(180, self::WINDOW_DAYS)),
                        'notes' => 'Seed: Active license generated for timeline',
                    ])
                    ->create();
            }

            $this->createHistoricalLicenses($account, $activeLicense);
        }
    }

    private function createUpgradeChains(): void
    {
        $accounts = Account::query()
            ->where('email', 'not like', '%@test.com')
            ->take(6)
            ->get();

        foreach ($accounts as $account) {
            $existingUpgradeHistory = License::query()
                ->where('used_by', $account->id)
                ->where('status', LicenseStatus::UPGRADED->value)
                ->exists();

            if ($existingUpgradeHistory) {
                continue;
            }

            $activeLicense = License::query()
                ->where('used_by', $account->id)
                ->where('status', LicenseStatus::ACTIVE->value)
                ->first();

            if (! $activeLicense) {
                continue;
            }

            $baseCreatedAt = $activeLicense->activated_at
                ? $activeLicense->activated_at->copy()->subDays(fake()->numberBetween(45, 150))
                : now()->subDays(fake()->numberBetween(120, 260));
            $baseActivatedAt = $baseCreatedAt->copy()->addDays(fake()->numberBetween(1, 7));
            $upgradeAt = $baseActivatedAt->copy()->addDays(fake()->numberBetween(20, 90));

            if ($upgradeAt->greaterThan(now()->subDay())) {
                $upgradeAt = now()->subDays(fake()->numberBetween(1, 14));
            }

            $basePrivilege = LicensePrivilege::STANDARD;
            if ($activeLicense->privilege === LicensePrivilege::STAFF) {
                $basePrivilege = fake()->randomElement([
                    LicensePrivilege::STANDARD,
                    LicensePrivilege::ULTIMATE,
                    LicensePrivilege::TESTER,
                ]);
            }

            License::create([
                'key' => \App\Services\LicenseService::generateLicenseKey(),
                'privilege' => $basePrivilege->value,
                'status' => LicenseStatus::UPGRADED->value,
                'used_by' => $account->id,
                'created_at' => $baseCreatedAt,
                'activated_at' => $baseActivatedAt,
                'expires_at' => $upgradeAt->copy()->addDays(fake()->numberBetween(30, 120)),
                'updated_at' => $upgradeAt,
                'notes' => 'Seed: Upgraded from this base license',
            ]);

            $shouldRetimelineActiveLicense = $activeLicense->notes === 'Seed: Active license generated for timeline';

            if ($shouldRetimelineActiveLicense && ($activeLicense->activated_at === null || $activeLicense->activated_at->lessThan($upgradeAt))) {
                $activeLicense->forceFill([
                    'activated_at' => $upgradeAt->copy()->addHours(fake()->numberBetween(1, 36)),
                    'created_at' => $baseCreatedAt->copy()->addDays(fake()->numberBetween(5, 30)),
                ])->save();
            }
        }
    }

    private function createHistoricalLicenses(Account $account, License $activeLicense): void
    {
        $historyCount = random_int(0, 2);

        for ($index = 0; $index < $historyCount; $index++) {
            $status = fake()->randomElement([
                LicenseStatus::EXPIRED,
                LicenseStatus::REVOKED,
                LicenseStatus::SUSPENDED,
            ]);

            $createdAt = $this->pickCreationTime($account->created_at);
            $activatedAt = $createdAt->copy()->addDays(fake()->numberBetween(1, 10));

            if ($activatedAt->greaterThan(now()->subDay())) {
                $activatedAt = now()->subDays(fake()->numberBetween(2, 20));
            }

            $record = [
                'key' => \App\Services\LicenseService::generateLicenseKey(),
                'privilege' => $activeLicense->privilege->value,
                'status' => $status->value,
                'used_by' => $account->id,
                'created_at' => $createdAt,
                'activated_at' => $activatedAt,
                'suspended_at' => null,
                'notes' => 'Seed: Historical license state',
            ];

            if ($status === LicenseStatus::EXPIRED) {
                $record['expires_at'] = $activatedAt->copy()->addDays(fake()->numberBetween(14, 120));
                if ($record['expires_at']->greaterThan(now()->subDay())) {
                    $record['expires_at'] = now()->subDays(fake()->numberBetween(1, 20));
                }
                $record['updated_at'] = $record['expires_at'];
            } elseif ($status === LicenseStatus::SUSPENDED) {
                $record['suspended_at'] = $activatedAt->copy()->addDays(fake()->numberBetween(7, 45));
                if ($record['suspended_at']->greaterThan(now()->subDay())) {
                    $record['suspended_at'] = now()->subDays(fake()->numberBetween(1, 15));
                }
                $record['expires_at'] = $record['suspended_at']->copy()->addDays(fake()->numberBetween(30, 180));
                $record['updated_at'] = $record['suspended_at'];
            } else {
                $record['expires_at'] = $activatedAt->copy()->addDays(fake()->numberBetween(90, self::WINDOW_DAYS));
                $record['updated_at'] = $activatedAt->copy()->addDays(fake()->numberBetween(5, 60));
                if ($record['updated_at']->greaterThan(now()->subDay())) {
                    $record['updated_at'] = now()->subDays(fake()->numberBetween(1, 10));
                }
            }

            License::create($record);
        }
    }

    private function pickCreationTime(?Carbon $accountCreatedAt): Carbon
    {
        $candidate = now()->subDays(fake()->numberBetween(30, self::WINDOW_DAYS));
        $latestAllowed = now()->subMinutes(30);

        if ($accountCreatedAt && $candidate->lessThan($accountCreatedAt)) {
            $candidate = $accountCreatedAt->copy()->addMinutes(fake()->numberBetween(10, 180));
        }

        if ($candidate->greaterThan($latestAllowed)) {
            $candidate = $latestAllowed->copy()->subMinutes(fake()->numberBetween(1, 30));
        }

        return $candidate;
    }

    private function pickActivationTime(?Carbon $accountCreatedAt): Carbon
    {
        $createdAt = $this->pickCreationTime($accountCreatedAt);

        $activationAt = $createdAt->copy()->addDays(fake()->numberBetween(1, 14));
        $latestAllowed = now()->subMinutes(5);

        if ($activationAt->greaterThan($latestAllowed)) {
            $activationAt = $latestAllowed->copy();
        }

        if ($activationAt->lessThanOrEqualTo($createdAt)) {
            $activationAt = $createdAt->copy()->addMinutes(fake()->numberBetween(5, 45));
        }

        return $activationAt;
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
            ['STD2U-67890-BCDEF-GHIJK-LMNOP', 'Upgrade token (2)', 'Cannot activate directly'],
            ['ULTIM-13579-CDEFG-HIJKL-MNOPQ', 'Ultimate (3)', 'Can activate'],
            ['STAFF-24680-DEFGH-IJKLM-NOPQR', 'Staff (7)', 'Can activate (gives admin)'],
            ['UPGRA-11223-34567-38ABC-DEFGH', 'Ultimate (3)', 'Can activate'],
            ['EXPIR-44556-67234-ABCDE-FGHIJ', 'Standard (1)', 'EXPIRED - Cannot activate'],
            ['REVOK-77889-90123-ABCDE-RABCD', 'Standard (1)', 'REVOKED - Cannot activate'],
            ['SUSPE-12345-23456-ABCDE-FGHIJ', 'Standard (1)', 'SUSPENDED - Cannot activate'],
            ['ACTIV-11111-22222-33333-44444', 'Standard (1)', 'ALREADY ACTIVE - Cannot activate'],
        ];

        $headers = ['License Key', 'Privilege', 'Status'];
        $this->command->table($headers, $testLicenses);

        $this->command->info('');
        $this->command->comment('Testing Scenarios:');
        $this->command->comment('1. Start with no active license → Try activating any UNUSED license');
        $this->command->comment('2. Have Standard → Try upgrading to Ultimate/Staff');
        $this->command->comment('3. Try activating Upgrade token directly (should fail)');
        $this->command->comment('4. Have higher tier → Try downgrading to Standard (should fail)');
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

        $rows = [];
        $total = 0;

        foreach (LicenseStatus::cases() as $status) {
            foreach (LicensePrivilege::cases() as $privilege) {
                $count = License::where('status', $status->value)
                    ->where('privilege', $privilege->value)
                    ->count();

                if ($count > 0) {
                    $rows[] = [$status->getLabel(), $privilege->getLabel(), $count];
                    $total += $count;
                }
            }
        }

        $this->command->table(['Status', 'Privilege', 'Count'], $rows);
        $this->command->info("Total licenses: {$total}");
        $this->command->info(str_repeat('-', 50));
    }
}
