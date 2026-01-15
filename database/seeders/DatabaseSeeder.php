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
            AccountSeeder::class,
            AccountDeviceSeeder::class,
            LicenseSeeder::class,
            EventLogSeeder::class,
            PackageReleaseSeeder::class,
            ClientSessionSeeder::class,
            UsageStatisticSeeder::class,
        ]);
    }
}
