<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountDevice;
use Illuminate\Database\Seeder;

class AccountDeviceSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = Account::all();

        foreach ($accounts as $account) {

            AccountDevice::factory()->withSpecificAccount($account->id)->create([
                'unbound_at' => null,
            ]);

            $unboundCount = rand(0, 2);
            if ($unboundCount > 0) {
                AccountDevice::factory()->count($unboundCount)->unbound()->withSpecificAccount($account->id)->create([
                    'account_id' => $account->id,
                ]);
            }

            if ($account->username === 'admin') {
                AccountDevice::factory()->create([
                    'account_id' => $account->id,
                    'hwid_hash' => hash('sha256', 'ADMIN_DEVICE_001'),
                    'user_agent' => 'Admin Console v1.0',
                    'ip_address' => '127.0.0.1',
                    'country_code' => 'US',
                    'device_fingerprint' => json_encode([
                        'screen_resolution' => '1920x1080',
                        'browser_plugins' => ['admin-tool'],
                        'timezone' => 'UTC',
                        'language' => 'en',
                        'platform' => 'Windows',
                        'hardware_concurrency' => 8,
                        'device_memory' => 16,
                    ]),
                    'first_seen_at' => now()->subMonths(3),
                    'last_seen_at' => now(),
                    'bound_at' => now()->subMonths(3),
                    'unbound_at' => null,
                    'created_at' => now()->subMonths(3),
                    'updated_at' => now(),
                ]);
            }
        }

        $suspiciousAccounts = Account::inRandomOrder()->take(5)->get();
        foreach ($suspiciousAccounts as $account) {
            for ($i = 0; $i < rand(2, 3); $i++) {
                AccountDevice::factory()->create([
                    'account_id' => $account->id,
                    'unbound_at' => null,
                    'bound_at' => now()->subDays(rand(1, 7)),
                    'ip_address' => '192.168.' . rand(1, 255) . '.' . rand(1, 255),
                    'country_code' => ['US', 'CN', 'JP'][rand(0, 2)],
                ]);
            }
        }

        $totalDevices = AccountDevice::count();
        $boundDevices = AccountDevice::whereNull('unbound_at')->count();
        $unboundDevices = AccountDevice::whereNotNull('unbound_at')->count();

        echo "Device Statistics: \n";
        echo "Total number of devices: " . $totalDevices . "\n";
        echo "Currently bound device: " . $boundDevices . "\n";
        echo "Unbound devices: " . $unboundDevices . "\n";
        echo "Average number of devices per Account: " . round($totalDevices / max(1, Account::count()), 2) . "\n";
    }
}
