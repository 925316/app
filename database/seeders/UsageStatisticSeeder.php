<?php

namespace Database\Seeders;

use App\Models\UsageStatistic;
use Illuminate\Database\Seeder;

class UsageStatisticSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing statistics
        UsageStatistic::truncate();

        // Create statistics
        $this->createGlobalStatistics();
        $this->createUserStatistics();
        $this->createLicenseStatistics();
        $this->createServerStatistics();
        $this->createRandomStatistics();
        $this->displayStatisticStats();
    }

    /**
     * Display usage statistic statistics.
     */
    private function displayStatisticStats(): void
    {
        $this->command->info(str_repeat('-', 50));
        $this->command->info('USAGE STATISTIC STATISTICS');
        $this->command->info(str_repeat('-', 50));

        $total = UsageStatistic::count();
        $global = UsageStatistic::where('stat_type', UsageStatistic::TYPE_GLOBAL)->count();
        $user = UsageStatistic::where('stat_type', UsageStatistic::TYPE_USER)->count();
        $license = UsageStatistic::where('stat_type', UsageStatistic::TYPE_LICENSE)->count();
        $server = UsageStatistic::where('stat_type', UsageStatistic::TYPE_SERVER)->count();

        $this->command->info("Total statistics: {$total}");
        $this->command->info("Global statistics: {$global}");
        $this->command->info("User statistics: {$user}");
        $this->command->info("License statistics: {$license}");
        $this->command->info("Server statistics: {$server}");

        // Show key distribution
        $keyStats = UsageStatistic::selectRaw('stat_key, count(*) as count')
            ->groupBy('stat_key')
            ->orderByDesc('count')
            ->get();

        if ($keyStats->isNotEmpty()) {
            $this->command->info('');
            $this->command->info('Key distribution:');
            foreach ($keyStats as $stat) {
                $this->command->info("  {$stat->stat_key}: {$stat->count}");
            }
        }

        $this->command->info(str_repeat('-', 50));
    }

    /**
     * Create global level statistics.
     */
    protected function createGlobalStatistics(): void
    {
        UsageStatistic::create([
            'stat_type' => UsageStatistic::TYPE_GLOBAL,
            'stat_key' => UsageStatistic::KEY_LOGIN_COUNT,
            'stat_value' => 453459,
        ]);

        UsageStatistic::create([
            'stat_type' => UsageStatistic::TYPE_GLOBAL,
            'stat_key' => UsageStatistic::KEY_USAGE_TIME,
            'stat_value' => 13802272, // ~26 years in minutes (26 * 365 * 24 * 60)
        ]);

        UsageStatistic::create([
            'stat_type' => UsageStatistic::TYPE_GLOBAL,
            'stat_key' => UsageStatistic::KEY_TOTAL_REQUESTS,
            'stat_value' => 12547893,
        ]);

        UsageStatistic::create([
            'stat_type' => UsageStatistic::TYPE_GLOBAL,
            'stat_key' => 'active_users',
            'stat_value' => 1542,
        ]);

        UsageStatistic::create([
            'stat_type' => UsageStatistic::TYPE_GLOBAL,
            'stat_key' => 'total_storage',
            'stat_value' => 2456.78,
        ]);
    }

    /**
     * Create user level statistics.
     */
    protected function createUserStatistics(): void
    {
        UsageStatistic::create([
            'stat_type' => UsageStatistic::TYPE_USER,
            'stat_key' => UsageStatistic::KEY_LOGIN_COUNT,
            'stat_value' => 650,
        ]);

        UsageStatistic::create([
            'stat_type' => UsageStatistic::TYPE_USER,
            'stat_key' => UsageStatistic::KEY_USAGE_TIME,
            'stat_value' => 575791, // ~1 year 1 month 1 day 20 hours in minutes
        ]);

        UsageStatistic::create([
            'stat_type' => UsageStatistic::TYPE_USER,
            'stat_key' => 'avg_session_duration',
            'stat_value' => 45.5,
        ]);

        UsageStatistic::create([
            'stat_type' => UsageStatistic::TYPE_USER,
            'stat_key' => 'files_uploaded',
            'stat_value' => 234,
        ]);

        UsageStatistic::create([
            'stat_type' => UsageStatistic::TYPE_USER,
            'stat_key' => 'api_calls',
            'stat_value' => 12345,
        ]);
    }

    /**
     * Create license level statistics.
     */
    protected function createLicenseStatistics(): void
    {
        UsageStatistic::create([
            'stat_type' => UsageStatistic::TYPE_LICENSE,
            'stat_key' => 'active_licenses',
            'stat_value' => 125,
        ]);

        UsageStatistic::create([
            'stat_type' => UsageStatistic::TYPE_LICENSE,
            'stat_key' => 'expired_licenses',
            'stat_value' => 23,
        ]);

        UsageStatistic::create([
            'stat_type' => UsageStatistic::TYPE_LICENSE,
            'stat_key' => 'license_usage_rate',
            'stat_value' => 85.5,
        ]);

        UsageStatistic::create([
            'stat_type' => UsageStatistic::TYPE_LICENSE,
            'stat_key' => 'renewals_due',
            'stat_value' => 15,
        ]);
    }

    /**
     * Create server level statistics.
     */
    protected function createServerStatistics(): void
    {
        UsageStatistic::create([
            'stat_type' => UsageStatistic::TYPE_SERVER,
            'stat_key' => 'server_uptime',
            'stat_value' => 99.98,
        ]);

        UsageStatistic::create([
            'stat_type' => UsageStatistic::TYPE_SERVER,
            'stat_key' => 'avg_response_time',
            'stat_value' => 125.5,
        ]);

        UsageStatistic::create([
            'stat_type' => UsageStatistic::TYPE_SERVER,
            'stat_key' => 'memory_usage',
            'stat_value' => 65.3,
        ]);

        UsageStatistic::create([
            'stat_type' => UsageStatistic::TYPE_SERVER,
            'stat_key' => 'cpu_usage',
            'stat_value' => 42.7,
        ]);

        UsageStatistic::create([
            'stat_type' => UsageStatistic::TYPE_SERVER,
            'stat_key' => 'disk_usage',
            'stat_value' => 78.2,
        ]);
    }

    /**
     * Create additional random statistics for testing.
     */
    protected function createRandomStatistics(): void
    {
        // Create 20 random statistics for variety
        UsageStatistic::factory()->count(20)->create();
    }
}
