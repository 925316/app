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
        // Get existing accounts and devices for realistic relationships
        $accounts = Account::take(10)->get();
        $devices = AccountDevice::whereIn('account_id', $accounts->pluck('id'))->get();

        // Create active sessions
        $this->createActiveSessions($accounts, $devices);

        // Create expired sessions
        $this->createExpiredSessions($accounts);

        // Create sessions with no heartbeat
        $this->createNoHeartbeatSessions($accounts);

        // Create version-specific sessions
        $this->createVersionSessions();

        // Create IP-specific sessions
        $this->createIpSessions();

        // Display session statistics
        $this->displaySessionStats();
    }

    /**
     * Create active client sessions.
     */
    private function createActiveSessions($accounts, $devices): void
    {
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
    }

    /**
     * Create expired client sessions.
     */
    private function createExpiredSessions($accounts): void
    {
        $expiredCount = max(5, $accounts->count() * 2);
        ClientSession::factory()
            ->count($expiredCount)
            ->expired()
            ->create();
    }

    /**
     * Create sessions with no heartbeat.
     */
    private function createNoHeartbeatSessions($accounts): void
    {
        $noHeartbeatCount = max(5, $accounts->count());
        ClientSession::factory()
            ->count($noHeartbeatCount)
            ->noHeartbeat()
            ->create();
    }

    /**
     * Create version-specific sessions.
     */
    private function createVersionSessions(): void
    {
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
    }

    /**
     * Create IP-specific sessions.
     */
    private function createIpSessions(): void
    {
        $testIps = ['16.59.46.1', '72.22.3.1', '1.56.19.46'];

        foreach ($testIps as $ip) {
            ClientSession::factory()
                ->count(2)
                ->ip($ip)
                ->active()
                ->create();
        }
    }

    /**
     * Display session statistics.
     */
    private function displaySessionStats(): void
    {
        $this->command->info(str_repeat('-', 50));
        $this->command->info('CLIENT SESSION STATISTICS');
        $this->command->info(str_repeat('-', 50));

        $total = ClientSession::count();
        $active = ClientSession::where('last_heartbeat_at', '>=', now()->subMinutes(30))->count();
        $expired = ClientSession::where('last_heartbeat_at', '<', now()->subMinutes(30))->count();
        $noHeartbeat = ClientSession::whereNull('last_heartbeat_at')->count();

        $this->command->table(
            ['Status', 'Count'],
            [
                ['Active Sessions', $active],
                ['Expired Sessions', $expired],
                ['No Heartbeat Sessions', $noHeartbeat],
                ['Total Sessions', $total],
            ]
        );

        // Show version distribution
        $versionStats = ClientSession::selectRaw('client_version, count(*) as count')
            ->groupBy('client_version')
            ->orderByDesc('count')
            ->get();

        if ($versionStats->isNotEmpty()) {
            $this->command->info('');
            $this->command->info('Version distribution:');
            foreach ($versionStats as $stat) {
                $this->command->info("  {$stat->client_version}: {$stat->count}");
            }
        }

        $this->command->info(str_repeat('-', 50));
    }
}
