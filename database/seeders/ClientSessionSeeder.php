<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\ClientSession;
use Illuminate\Database\Seeder;

class ClientSessionSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = Account::all();

        if ($accounts->isEmpty()) {
            return;
        }

        foreach ($accounts as $account) {
            ClientSession::factory()->create([
                'account_id' => $account->id,
                'last_heartbeat_at' => now()->subMinutes(rand(1, 30)),
            ]);

            if ($account->username === 'admin') {
                ClientSession::factory()->adminSession()->create([
                    'account_id' => $account->id,
                    'session_token' => hash('sha512', 'admin_session_' . time()),
                ]);
            }
        }

        $accountsForExpired = $accounts->random(ceil($accounts->count() * 0.5));
        foreach ($accountsForExpired as $account) {
            $expiredCount = rand(1, 3);
            ClientSession::factory()->count($expiredCount)->expired()->create([
                'account_id' => $account->id,
            ]);
        }

        $totalSessions = rand(250, 350);
        $existingCount = ClientSession::count();
        $remaining = max(0, $totalSessions - $existingCount);

        if ($remaining > 0) {
            ClientSession::factory()->count($remaining)->create([
                'account_id' => function () use ($accounts) {
                    return $accounts->random()->id;
                },
            ]);
        }

        echo "about " . ClientSession::count() . " client sessions created.\n";
    }
}
