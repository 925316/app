<?php

namespace Database\Seeders;

use App\Enums\LicenseStatus;
use App\Models\Account;
use App\Models\ClientSession;
use App\Models\License;
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
        $accountCount = Account::count();
        $sessionCount = ClientSession::count();
        $activeSessionCount = ClientSession::query()
            ->where('last_heartbeat_at', '>=', now()->subMinutes(5))
            ->count();

        $estimatedLoginCount = max($accountCount * fake()->numberBetween(12, 35), $sessionCount * fake()->numberBetween(4, 9));
        $estimatedUsageMinutes = max($sessionCount * fake()->numberBetween(120, 960), $accountCount * fake()->numberBetween(240, 1440));
        $estimatedTotalRequests = max($estimatedLoginCount * fake()->numberBetween(25, 80), $sessionCount * fake()->numberBetween(300, 1400));

        UsageStatistic::create([
            'stat_type' => UsageStatistic::TYPE_GLOBAL,
            'stat_key' => UsageStatistic::KEY_LOGIN_COUNT,
            'stat_value' => $estimatedLoginCount,
        ]);

        UsageStatistic::create([
            'stat_type' => UsageStatistic::TYPE_GLOBAL,
            'stat_key' => UsageStatistic::KEY_USAGE_TIME,
            'stat_value' => $estimatedUsageMinutes,
        ]);

        UsageStatistic::create([
            'stat_type' => UsageStatistic::TYPE_GLOBAL,
            'stat_key' => UsageStatistic::KEY_TOTAL_REQUESTS,
            'stat_value' => $estimatedTotalRequests,
        ]);

        UsageStatistic::create([
            'stat_type' => UsageStatistic::TYPE_GLOBAL,
            'stat_key' => 'active_users',
            'stat_value' => min($accountCount, max($activeSessionCount, 1)),
        ]);
    }

    /**
     * Create license level statistics.
     */
    private function createLicenseStatistics(): void
    {
        $activeLicenses = License::query()->where('status', LicenseStatus::ACTIVE->value)->count();
        $expiredLicenses = License::query()->where('status', LicenseStatus::EXPIRED->value)->count();
        $upgradedLicenses = License::query()->where('status', LicenseStatus::UPGRADED->value)->count();

        UsageStatistic::create([
            'stat_type' => UsageStatistic::TYPE_LICENSE,
            'stat_key' => 'active_licenses',
            'stat_value' => $activeLicenses,
        ]);

        UsageStatistic::create([
            'stat_type' => UsageStatistic::TYPE_LICENSE,
            'stat_key' => 'expired_licenses',
            'stat_value' => $expiredLicenses,
        ]);

        UsageStatistic::create([
            'stat_type' => UsageStatistic::TYPE_LICENSE,
            'stat_key' => 'upgraded_licenses',
            'stat_value' => $upgradedLicenses,
        ]);
    }
}
