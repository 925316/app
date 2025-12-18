<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\License;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        Account::factory()->create([
            'username' => 'TestAccount',
            'email' => 'test@example.com',
            'password' => Hash::make('admin123'),
            'email_verified_at' => null,
        ]);

        $adminLicense = License::where('key', 'ADMIN-00000-00000-00000-00000')->first();
        $testerLicense = License::where('key', 'TEST0-00000-00000-00000-00000')->first();

        Account::factory()->create([
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'),
            'license_id' => $adminLicense->id,
            'email_verified_at' => now()->subMonths(6),
        ]);

        Account::factory()->create([
            'username' => 'tester',
            'email' => 'tester@example.com',
            'password' => Hash::make('tester123'),
            'license_id' => $testerLicense->id,
            'email_verified_at' => now()->subMonths(6),
        ]);

        $accounts = Account::factory()->count(100)->create();
        $availableLicenses = License::where('status', 0)->get();
        $accountsToAssign = $accounts->take(min(45, $availableLicenses->count()));

        foreach ($accountsToAssign as $index => $account) {
            if (isset($availableLicenses[$index])) {
                $license = $availableLicenses[$index];
                $account->update(['license_id' => $license->id]);
                $license->update([
                    'used_by' => $account->id,
                    'status' => 1,
                    'activated_at' => now()->subDays(rand(1, 365)),
                ]);
            }
        }

        Account::factory()->count(15)->suspended()->create();
        Account::factory()->count(10)->unverified()->create();

        echo "about " . Account::count() . " accounts created.\n";
    }
}
