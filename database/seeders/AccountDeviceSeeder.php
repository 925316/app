<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountDevice;
use Illuminate\Database\Seeder;

class AccountDeviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we have accounts first
        if (Account::count() === 0) {
            $this->command->warn('No accounts found. Creating 50 sample accounts first...');
            Account::factory()->count(50)->create();
        }

        $this->createPrimaryDeviceBindings();
        $this->createHistoricalDevices();
        $this->displayDeviceStats();
    }

    /**
     * Create primary device bindings for accounts.
     * Each account gets exactly one currently bound device.
     */
    private function createPrimaryDeviceBindings(): void
    {
        $accounts = Account::all();

        foreach ($accounts as $account) {
            // Check if account already has a bound device
            $existingBound = AccountDevice::where('account_id', $account->id)
                ->whereNotNull('bound_at')
                ->whereNull('unbound_at')
                ->exists();

            if (! $existingBound) {
                $firstSeen = now()->subDays(fake()->numberBetween(30, 365));
                if ($account->created_at && $firstSeen->lessThan($account->created_at)) {
                    $firstSeen = $account->created_at->copy()->addDays(fake()->numberBetween(0, 7));
                }
                $boundAt = $firstSeen->copy()->addDays(fake()->numberBetween(1, 7));
                $lastSeen = $boundAt->copy()->addDays(fake()->numberBetween(0, 30));
                if ($lastSeen->greaterThan(now())) {
                    $lastSeen = now()->subHours(fake()->numberBetween(1, 72));
                }

                AccountDevice::factory()
                    ->for($account)
                    ->create([
                        'first_seen_at' => $firstSeen,
                        'last_seen_at' => $lastSeen,
                        'bound_at' => $boundAt,
                        'unbound_at' => null, // Currently bound
                    ]);
            }
        }

        $this->command->info("Created primary device bindings for {$accounts->count()} accounts");
    }

    /**
     * Create historical unbound devices for accounts.
     * Each account may have multiple historical devices, but only one currently bound.
     */
    private function createHistoricalDevices(): void
    {
        $accounts = Account::all();

        foreach ($accounts as $account) {
            // Random number of historical devices (0-3 per account)
            $historicalCount = fake()->numberBetween(0, 3);

            for ($i = 0; $i < $historicalCount; $i++) {
                $firstSeen = now()->subDays(fake()->numberBetween(60, 340));
                if ($account->created_at && $firstSeen->lessThan($account->created_at)) {
                    $firstSeen = $account->created_at->copy()->addDays(fake()->numberBetween(0, 10));
                }

                if ($firstSeen->greaterThan(now()->subDays(2))) {
                    $firstSeen = now()->subDays(fake()->numberBetween(7, 60));
                }

                $bindDate = $firstSeen->copy()->addDays(fake()->numberBetween(1, 7));
                $unbindDate = $bindDate->copy()->addDays(fake()->numberBetween(7, 120));

                if ($unbindDate->greaterThan(now()->subDay())) {
                    $unbindDate = now()->subDays(fake()->numberBetween(1, 14));
                }

                $lastSeen = $unbindDate->copy()->subDays(fake()->numberBetween(1, 7));

                AccountDevice::factory()
                    ->for($account)
                    ->create([
                        'first_seen_at' => $firstSeen,
                        'last_seen_at' => $lastSeen,
                        'bound_at' => $bindDate,
                        'unbound_at' => $unbindDate,
                    ]);
            }
        }

        $this->command->info('Created historical devices for multiple accounts');
    }

    /**
     * Display device statistics.
     */
    private function displayDeviceStats(): void
    {
        $this->command->info(str_repeat('-', 50));
        $this->command->info('DEVICE STATISTICS');
        $this->command->info(str_repeat('-', 50));

        $total = AccountDevice::count();
        $bound = AccountDevice::whereNotNull('bound_at')->whereNull('unbound_at')->count();
        $unbound = AccountDevice::whereNotNull('unbound_at')->count();
        $neverBound = AccountDevice::whereNull('bound_at')->count();
        $active = AccountDevice::where('last_seen_at', '>=', now()->subDays(7))->count();
        $inactive = AccountDevice::where('last_seen_at', '<', now()->subDays(7))->count();

        $this->command->info("Total devices: {$total}");
        $this->command->info("Currently bound: {$bound}");
        $this->command->info("Previously unbound: {$unbound}");
        $this->command->info("Never bound: {$neverBound}");
        $this->command->info("Active (seen in last 7 days): {$active}");
        $this->command->info("Inactive (not seen in last 7 days): {$inactive}");

        // Show accounts with currently bound devices
        $accountsWithBoundDevices = Account::whereHas('devices', function ($query) {
            $query->whereNotNull('bound_at')
                ->whereNull('unbound_at');
        })
            ->with(['devices' => function ($query) {
                $query->whereNotNull('bound_at')
                    ->whereNull('unbound_at')
                    ->orderBy('last_seen_at', 'desc')
                    ->limit(1);
            }])
            ->limit(10)
            ->get();

        if ($accountsWithBoundDevices->isNotEmpty()) {
            $this->command->info('');
            $this->command->info('Accounts with currently bound devices:');
            foreach ($accountsWithBoundDevices as $account) {
                if ($device = $account->devices->first()) {
                    $this->command->info("Account #{$account->id}: Bound device #{$device->id} (HWID: {$device->hwid_hash})");
                }
            }
        }

        // Show device count per account (including historical devices)
        $devicesPerAccount = Account::withCount('devices')
            ->orderByDesc('devices_count')
            ->limit(3)
            ->get();

        if ($devicesPerAccount->isNotEmpty()) {
            $this->command->info('');
            $this->command->info('Top 3 accounts by total device history (including unbound):');
            foreach ($devicesPerAccount as $account) {
                $this->command->info("Account #{$account->id}: {$account->devices_count} total devices (historical)");
            }
        }

        $this->command->info(str_repeat('-', 50));
    }
}
