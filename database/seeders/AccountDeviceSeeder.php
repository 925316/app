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
        $this->createTestCases();
        $this->displayDeviceStats();
    }

    /**
     * Create primary device bindings for accounts.
     */
    private function createPrimaryDeviceBindings(): void
    {
        $accounts = Account::limit(30)->get();

        foreach ($accounts as $account) {
            AccountDevice::factory()
                ->count(1)
                ->for($account)
                ->create();
        }

        $this->command->info("Created primary device bindings for {$accounts->count()} accounts");
    }

    /**
     * Create specific test cases for development.
     */
    private function createTestCases(): void
    {
        // First, ensure all accounts have at most one currently bound device
        $this->ensureSingleBoundDevicePerAccount();

        // Test case 1: Account with one currently bound device and historical unbound devices
        $multiDeviceAccount = Account::first();
        if ($multiDeviceAccount) {
            // Create historical unbound devices
            AccountDevice::factory()
                ->count(2)
                ->for($multiDeviceAccount)
                ->unbound()
                ->create();
        }

        // Test case 2: Account with mixed device statuses (only one bound at a time)
        $mixedStatusAccount = Account::skip(1)->first();
        if ($mixedStatusAccount) {
            // Create historical unbound devices
            AccountDevice::factory()
                ->for($mixedStatusAccount)
                ->unbound()
                ->create();

            // Create never bound devices
            AccountDevice::factory()
                ->for($mixedStatusAccount)
                ->neverBound()
                ->create();
        }

        // Test case 3: Devices from specific countries (each with different accounts)
        $countries = [
            ['US', 5],
            ['GB', 3],
            ['JP', 4],
            ['CN', 6],
        ];

        foreach ($countries as [$countryCode, $count]) {
            // Create devices for different accounts to avoid multiple bound devices per account
            $accounts = Account::inRandomOrder()->limit($count)->get();
            foreach ($accounts as $account) {
                AccountDevice::factory()
                    ->for($account)
                    ->country($countryCode)
                    ->unbound() // Make these unbound to avoid conflicts
                    ->create();
            }
        }

        // Test case 4: Inactive devices (not seen for >60 days)
        // Create for different accounts to avoid conflicts
        $accounts = Account::inRandomOrder()->limit(10)->get();
        foreach ($accounts as $account) {
            AccountDevice::factory()
                ->for($account)
                ->inactive(60)
                ->unbound() // Make these unbound to avoid conflicts
                ->create();
        }

        // Test case 5: Recently unbound devices
        $accounts = Account::inRandomOrder()->limit(5)->get();
        foreach ($accounts as $account) {
            AccountDevice::factory()
                ->for($account)
                ->state([
                    'bound_at' => now()->subDays(30),
                    'unbound_at' => now()->subDays(1),
                ])
                ->create();
        }

        // Test case 6: Devices from local network
        $accounts = Account::inRandomOrder()->limit(5)->get();
        foreach ($accounts as $account) {
            AccountDevice::factory()
                ->for($account)
                ->localNetwork()
                ->unbound() // Make these unbound to avoid conflicts
                ->create();
        }
    }

    /**
     * Ensure each account has at most one currently bound device.
     */
    private function ensureSingleBoundDevicePerAccount(): void
    {
        $accounts = Account::all();

        foreach ($accounts as $account) {
            // Get all currently bound devices for this account
            $boundDevices = AccountDevice::where('account_id', $account->id)
                ->whereNotNull('bound_at')
                ->whereNull('unbound_at')
                ->orderBy('bound_at', 'desc')
                ->get();

            // If there are multiple bound devices, keep only the most recent one
            if ($boundDevices->count() > 1) {
                $mostRecentDevice = $boundDevices->first();
                $devicesToUnbind = $boundDevices->slice(1);

                foreach ($devicesToUnbind as $device) {
                    $device->update(['unbound_at' => now()]);
                }
            }
        }
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
