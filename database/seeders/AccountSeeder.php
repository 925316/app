<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        Account::factory()->create([
            'username' => 'TestAccount',
            'email' => 'test@example.com',
            'password' => Hash::make('test123'),
            'email_verified_at' => null,
            'last_login_at' => now()->subDays(5),
            'last_ip_address' => '192.168.1.100',
        ]);
        
        Account::factory()->create([
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'),
            'email_verified_at' => now()->subMonths(6),
            'last_login_at' => now()->subHours(2),
            'last_ip_address' => '10.0.0.1',
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1', 'recovery-code-2'])),
            'two_factor_confirmed_at' => now()->subMonths(3),
        ]);
        
        Account::factory()->create([
            'username' => 'tester',
            'email' => 'tester@example.com',
            'password' => Hash::make('tester123'),
            'email_verified_at' => now()->subMonths(6),
            'last_login_at' => now()->subDays(1),
            'last_ip_address' => '172.16.0.100',
        ]);
        
        Account::factory()->create([
            'username' => 'hwid_user',
            'email' => 'hwid@example.com',
            'password' => Hash::make('hwid123'),
            'email_verified_at' => now()->subMonths(3),
            'hwid_reset_count' => 3,
            'hwid_last_reset_at' => now()->subDays(10),
        ]);
        
        Account::factory()->suspended('Multiple Failed Login Attempts', now()->addDays(7))->create([
            'username' => 'suspended_temp',
            'email' => 'suspended_temp@example.com',
            'password' => Hash::make('temp123'),
            'email_verified_at' => now()->subMonths(2),
        ]);
        
        Account::factory()->suspended('Violation of Terms of Service', null)->create([
            'username' => 'banned_user',
            'email' => 'banned@example.com',
            'password' => Hash::make('banned123'),
            'email_verified_at' => now()->subMonths(4),
        ]);

        // Create sample accounts with different states
        Account::factory()->count(100)->create();
        
        // Create some verified accounts
        Account::factory()->count(5)->verified()->create();
        
        // Create some accounts with 2FA enabled
        Account::factory()->count(3)->withTwoFactor()->verified()->create();
        
        // Create some suspended accounts
        Account::factory()->count(2)->suspended()->create();
        
        // Create some recently active accounts
        Account::factory()->count(4)->recentlyActive()->verified()->create();
        
        // Create accounts with HWID resets
        Account::factory()->count(3)->withHwidResets()->create();
        
        // Create unverified accounts
        Account::factory()->count(3)->unverified()->create();
    }
}