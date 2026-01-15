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
    }
}