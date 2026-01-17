<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\License;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminAccountSeeder extends Seeder
{
    public function run(): void
    {
        // Check if admin account already exists
        $admin = Account::where('email', 'admin@example.com')->first();

        if (! $admin) {
            // Create admin account
            $admin = Account::create([
                'username' => 'admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
                'last_login_at' => now(),
                'last_ip_address' => '127.0.0.1',
            ]);

            $this->command->info('Admin account created successfully!');
        } else {
            $this->command->info('Admin account already exists, skipping creation.');
        }

        // Check if admin already has an active license
        $adminLicense = License::where('used_by', $admin->id)
            ->where('status', 1) // active
            ->where('privilege', 5) // staff
            ->first();

        if (! $adminLicense) {
            // Create an active license with staff privilege (level 5)
            License::create([
                'key' => 'ADMIN-LICENSE-KEY-12345',
                'type' => 1, // base
                'privilege' => 5, // staff
                'status' => 1, // active
                'used_by' => $admin->id,
                'expires_at' => now()->addYears(10),
                'activated_at' => now(),
                'created_from_ip' => '127.0.0.1',
                'notes' => 'Administrator license with full privileges',
            ]);

            $this->command->info('Admin license created successfully!');
        } else {
            $this->command->info('Admin license already exists, skipping creation.');
        }

        // Check if test user account already exists
        $user = Account::where('email', 'user@example.com')->first();

        if (! $user) {
            // Create a regular user account for testing
            $user = Account::create([
                'username' => 'user',
                'email' => 'user@example.com',
                'password' => Hash::make('user123'),
                'email_verified_at' => now(),
                'last_login_at' => now(),
                'last_ip_address' => '127.0.0.1',
            ]);

            $this->command->info('Test user account created successfully!');
        } else {
            $this->command->info('Test user account already exists, skipping creation.');
        }

        // Check if user already has an active license
        $userLicense = License::where('used_by', $user->id)
            ->where('status', 1) // active
            ->where('privilege', 1) // basic
            ->first();

        if (! $userLicense) {
            // Create a regular license with basic privilege (level 1)
            License::create([
                'key' => 'USER-LICENSE-KEY-12345',
                'type' => 1, // base
                'privilege' => 1, // basic
                'status' => 1, // active
                'used_by' => $user->id,
                'expires_at' => now()->addYear(),
                'activated_at' => now(),
                'created_from_ip' => '127.0.0.1',
                'notes' => 'Regular user license',
            ]);

            $this->command->info('Test user license created successfully!');
        } else {
            $this->command->info('Test user license already exists, skipping creation.');
        }
    }
}
