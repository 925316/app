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

        // Create simplified statistics
        $this->createGlobalStatistics();
        $this->createLicenseStatistics();
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
        $license = UsageStatistic::where('stat_type', UsageStatistic::TYPE_LICENSE)->count();

        $this->command->info("Total statistics: {$total}");
        $this->command->info("Global statistics: {$global}");
        $this->command->info("License statistics: {$license}");

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
    private function createGlobalStatistics(): void
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
    }

    /**
     * Create license level statistics.
     */
    private function createLicenseStatistics(): void
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
            'stat_key' => 'upgraded_licenses',
            'stat_value' => 45,
        ]);
    }
}
