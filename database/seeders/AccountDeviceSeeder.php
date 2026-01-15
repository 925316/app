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
        // Check if there are accounts, if not, create some
        if (Account::count() === 0) {
            Account::factory()->count(50)->create();
        }

        $accounts = Account::limit(50)->get();

        foreach ($accounts as $account) {
            AccountDevice::factory()
                ->count(1)
                ->for($account)
                ->create();
        }

        // Create specific test cases
        $this->createTestCases();
    }

    /**
     * Create specific test cases for development.
     */
    private function createTestCases(): void
    {
        // Test case 1: Account with multiple bound devices
        $multiDeviceAccount = Account::first();
        if ($multiDeviceAccount) {
            AccountDevice::factory()
                ->for($multiDeviceAccount)
                ->bound()
                ->active()
                ->create();

            AccountDevice::factory()
                ->count(2)
                ->for($multiDeviceAccount)
                ->unbound()
                ->create();
        }

        // Test case 2: Account with mixed device statuses
        $mixedStatusAccount = Account::skip(1)->first();
        if ($mixedStatusAccount) {
            AccountDevice::factory()
                ->for($mixedStatusAccount)
                ->bound()
                ->active()
                ->create();

            AccountDevice::factory()
                ->for($mixedStatusAccount)
                ->unbound()
                ->create();

            AccountDevice::factory()
                ->for($mixedStatusAccount)
                ->neverBound()
                ->create();
        }

        // Test case 3: Device from specific countries
        $countries = [
            ['US', 5],
            ['GB', 3],
            ['JP', 4],
            ['CN', 6],
        ];

        foreach ($countries as [$countryCode, $count]) {
            AccountDevice::factory()
                ->count($count)
                ->country($countryCode)
                ->bound()
                ->active()
                ->create();
        }

        // Test case 4: Inactive devices (not seen for >60 days)
        AccountDevice::factory()
            ->count(10)
            ->inactive(60)
            ->create();

        // Test case 5: Recently unbound devices
        AccountDevice::factory()
            ->count(5)
            ->state([
                'bound_at' => now()->subDays(30),
                'unbound_at' => now()->subDays(1),
            ])
            ->create();

        // Test case 6: Devices from local network
        AccountDevice::factory()
            ->count(5)
            ->localNetwork()
            ->bound()
            ->active()
            ->create();

    }
}
