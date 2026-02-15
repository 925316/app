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

        // Create current active sessions (one per account+device combo)
        $this->createActiveSessions($accounts, $devices);

        // Create historical expired sessions
        $this->createHistoricalSessions($accounts);

        // Display session statistics
        $this->displaySessionStats();
    }

    /**
     * Create active client sessions.
     * Each account+device combination has at most one active session.
     */
    private function createActiveSessions($accounts, $devices): void
    {
        $accounts->each(function ($account) use ($devices) {
            $accountDevices = $devices->where('account_id', $account->id);

            if ($accountDevices->isNotEmpty()) {
                $accountDevices->each(function ($device) {
                    // Only create one active session per device
                    // Check if an active session already exists
                    $existingActive = ClientSession::where('device_id', $device->id)
                        ->where('last_heartbeat_at', '>=', now()->subMinutes(30))
                        ->exists();

                    if (! $existingActive) {
                        ClientSession::factory()
                            ->forDevice($device)
                            ->active()
                            ->create();
                    }
                });
            }
        });

        $this->command->info('Created active sessions (one per device)');
    }

    /**
     * Create historical expired sessions.
     */
    private function createHistoricalSessions($accounts): void
    {
        $devices = AccountDevice::whereIn('account_id', $accounts->pluck('id'))->get();

        foreach ($devices as $device) {
            // Random number of historical sessions (0-2 per device)
            $historicalCount = rand(0, 2);

            for ($i = 0; $i < $historicalCount; $i++) {
                $createdTime = now()->subDays(rand(30, 180));
                $heartbeatTime = $createdTime->copy()->addHours(rand(1, 24));

                ClientSession::factory()
                    ->forDevice($device)
                    ->create([
                        'created_at' => $createdTime,
                        'updated_at' => $heartbeatTime,
                        'last_heartbeat_at' => $heartbeatTime,
                    ]);
            }
        }

        $this->command->info('Created historical expired sessions');
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
