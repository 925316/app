<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountDevice;
use App\Models\ClientSession;
use Illuminate\Database\Seeder;

class ClientSessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = Account::take(15)->get();
        $devices = AccountDevice::whereIn('account_id', $accounts->pluck('id'))->get();

        $accounts->each(function ($account) use ($devices) {
            $accountDevices = $devices->where('account_id', $account->id);

            if ($accountDevices->isNotEmpty()) {
                $accountDevices->each(function ($device) {
                    $sessionCount = rand(0, 2);

                    ClientSession::factory()
                        ->count($sessionCount)
                        ->forDevice($device)
                        ->active()
                        ->create();
                });
            }
        });

        $expiredCount = max(10, $accounts->count() * 2);
        ClientSession::factory()
            ->count($expiredCount)
            ->expired()
            ->create();

        $noHeartbeatCount = max(5, $accounts->count());
        ClientSession::factory()
            ->count($noHeartbeatCount)
            ->noHeartbeat()
            ->create();

        $testVersions = [
            '1.0.0' => 3,
            '2.0.0' => 4,
            '2.2.5' => 2,
        ];

        foreach ($testVersions as $version => $count) {
            ClientSession::factory()
                ->count($count)
                ->version($version)
                ->create();
        }

        $testIps = ['192.168.1.100', '10.0.0.50', '172.16.0.25'];

        foreach ($testIps as $ip) {
            ClientSession::factory()
                ->count(2)
                ->ip($ip)
                ->active()
                ->create();
        }

        $this->command->table(
            ['Status', 'Count'],
            [
                ['Active Sessions', ClientSession::active()->count()],
                ['Expired Sessions', ClientSession::expired()->count()],
                ['Total Sessions', ClientSession::count()],
            ]
        );
    }

    /**
     * Clean up old sessions before seeding
     */
    private function cleanupOldSessions(): void
    {
        ClientSession::where('created_at', '<', now()->subDays(30))->delete();
    }
}
