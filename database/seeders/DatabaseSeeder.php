<?php

namespace Database\Seeders;

use App\Models\Account;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Account::factory(10)->create();

        Account::factory()->create([
            'username' => 'TestAccount',
            'email' => 'test@example.com',
            'password' => Hash::make('admin123'),
        ]);
    }
}
