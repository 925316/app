<?php

namespace Database\Seeders;

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
        License::factory()
            ->count(15)
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

        License::factory()
            ->count(25)
            ->active()
            ->assigned()
            ->base()
            ->privilege(2) // regular
            ->create();

        License::factory()
            ->count(20)
            ->active()
            ->assigned()
            ->base()
            ->privilege(3) // ultimate
            ->create();

        License::factory()
            ->count(2)
            ->active()
            ->assigned()
            ->base()
            ->privilege(4) // tester
            ->create();

        License::factory()
            ->count(2)
            ->active()
            ->assigned()
            ->base()
            ->privilege(5) // staff
            ->create();

        License::factory()
            ->count(12)
            ->suspended()
            ->assigned()
            ->base()
            ->privilege(2) // regular
            ->create();

        License::factory()
            ->count(18)
            ->expired()
            ->assigned()
            ->base()
            ->privilege(2) // regular
            ->create();

        License::factory()
            ->count(7)
            ->unused()
            ->upgrade()
            ->privilege(3) // ultimate
            ->create();

        $this->createLicensesForAccounts();

        $this->displayLicenseStats();
    }

    /**
     * Create licenses assigned to specific accounts.
     */
    private function createLicensesForAccounts(): void
    {
        if (! Account::exists()) {
            $this->command->warn('No accounts found. Skipping account-specific licenses.');

            return;
        }

        $accounts = Account::take(10)->get();

        foreach ($accounts as $account) {
            License::factory()
                ->active()
                ->state([
                    'used_by' => $account->id,
                    'activated_at' => fake()->dateTimeBetween('-6 months', '-1 week'),
                ])
                ->create();

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
                            License::STATUS_UPGRADED,
                            License::STATUS_EXPIRED,
                            License::STATUS_REVOKED,
                        ]);

                        return ['status' => $status];
                    })
                    ->create();

                $this->command->info("  Account #{$account->id}: {$otherLicenseCount} non-active license(s)");
            }
        }
    }

    /**
     * Display license statistics.
     */
    private function displayLicenseStats(): void
    {
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
