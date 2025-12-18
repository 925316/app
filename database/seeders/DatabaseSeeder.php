<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            LicenseSeeder::class,
            AccountSeeder::class,
            AccountDeviceSeeder::class,
            ClientSessionSeeder::class,
            
            PackageReleaseSeeder::class,
            UsageStatisticSeeder::class,
            
            UpgradeSeeder::class,
            EventLogSeeder::class,
            IpRateLimitSeeder::class,
        ]);
    }
}
