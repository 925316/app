<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\License;
use App\Models\Upgrade;
use Illuminate\Database\Seeder;

class UpgradeSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = Account::whereNotNull('license_id')->whereHas('license', function ($query) {
            $query->where('status', 1);
        })->get();

        $upgradeCount = 0;
        $maxUpgrades = min(ceil($accounts->count() * 0.4), 50);

        foreach ($accounts as $account) {

            if (rand(1, 100) <= 30 && $upgradeCount < $maxUpgrades) {
                $this->createUpgradeForAccount($account);
                $upgradeCount++;
            }
        }

        Upgrade::factory()->count(50)->create();

        echo "about " . Upgrade::count() . " upgrades created.\n";
    }

    private function createUpgradeForAccount(Account $account): void
    {
        $fromLicense = $account->license;

        $fromPrivilege = $fromLicense->privilege;
        $toPrivilege = $this->calculateUpgradePrivilege($fromPrivilege);

        if ($toPrivilege <= $fromPrivilege) {
            return;
        }

        $toLicense = License::create([
            'key' => $this->generateUpgradeKey($fromLicense->key, $toPrivilege),
            'type' => 2, // upgrade license
            'privilege' => $toPrivilege,
            'status' => 1, // active
            'used_by' => $account->id,
            'activated_at' => now(),
            'expires_at' => $fromLicense->expires_at,
            'created_from_ip' => $fromLicense->created_from_ip ?? '127.0.0.1',
            'notes' => 'System upgrade',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Upgrade::create([
            'account_id' => $account->id,
            'license_from' => $fromLicense->id,
            'license_to' => $toLicense->id,
            'notes' => 'user ' . $this->privilegeName($fromPrivilege) . ' upgraded to ' . $this->privilegeName($toPrivilege),
            'created_at' => now()->subDays(rand(1, 365)),
            'updated_at' => now()->subDays(rand(1, 365)),
        ]);

        $fromLicense->update(['status' => 4]); // upgraded

        $account->update(['license_id' => $toLicense->id]);
    }

    private function createHistoricalUpgrades(): void
    {
        $upgradedLicenses = License::where('status', 4)->get();

        foreach ($upgradedLicenses->take(10) as $upgradedLicense) {
            $upgradeRecord = Upgrade::where('license_to', $upgradedLicense->id)->first();

            if ($upgradeRecord) {

                $beforeUpgrade = License::where('id', $upgradeRecord->license_from)->first();

                if ($beforeUpgrade) {
                    $historicalFromLicense = License::create([
                        'key' => $this->generateHistoricalKey($beforeUpgrade->key),
                        'type' => 1,
                        'privilege' => max(1, $beforeUpgrade->privilege - 1),
                        'status' => 4,
                        'used_by' => $upgradeRecord->account_id,
                        'activated_at' => $beforeUpgrade->activated_at,
                        'expires_at' => $beforeUpgrade->expires_at,
                        'notes' => '',
                        'created_at' => $beforeUpgrade->created_at,
                        'updated_at' => $beforeUpgrade->updated_at,
                    ]);

                    Upgrade::create([
                        'account_id' => $upgradeRecord->account_id,
                        'license_from' => $historicalFromLicense->id,
                        'license_to' => $beforeUpgrade->id,
                        'notes' => '',
                        'created_at' => $beforeUpgrade->created_at,
                        'updated_at' => $beforeUpgrade->updated_at,
                    ]);
                }
            }
        }
    }

    private function calculateUpgradePrivilege(int $currentPrivilege): int
    {
        $upgradeAmount = rand(1, 2);
        return min(5, $currentPrivilege + $upgradeAmount);
    }

    private function generateUpgradeKey(string $originalKey, int $newPrivilege): string
    {
        $parts = [];
        for ($i = 0; $i < 5; $i++) {
            $part = '';
            for ($j = 0; $j < 5; $j++) {
                $part .= chr(rand(65, 90)); // A-Z
            }
            $parts[] = $part;
        }

        return implode('-', $parts);
    }

    private function generateHistoricalKey(string $originalKey): string
    {
        return 'HIST-' . substr(md5($originalKey), 0, 8) . '-' . $originalKey;
    }

    private function privilegeName(int $privilege): string
    {
        $names = [
            1 => 'basic',
            2 => 'regular',
            3 => 'ultimate',
            4 => 'tester',
            5 => 'staff',
        ];

        return $names[$privilege] ?? 'unknown';
    }
}
