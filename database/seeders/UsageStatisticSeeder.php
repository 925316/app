<?php

namespace Database\Seeders;

use App\Enums\LicenseStatus;
use App\Models\Account;
use App\Models\License;
use App\Models\UsageStatistic;
use App\Services\StatisticsService;
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
        StatisticsService::updateGlobalStatistics();

        $stats = StatisticsService::getGlobalStatistics();
        $onlineUsers = (int) ($stats['active_users'] ?? 0);
        $dailyActiveUsers = (int) ($stats['daily_active_users'] ?? 0);
        $recentActiveUsers = (int) ($stats['recent_active_users'] ?? 0);
        $accountCount = (int) ($stats['total_accounts'] ?? 0);
        $dormantUsers = max($accountCount - $recentActiveUsers, 0);

        UsageStatistic::updateOrCreate(
            [
                'stat_type' => UsageStatistic::TYPE_GLOBAL,
                'stat_key' => UsageStatistic::KEY_USAGE_TIME,
            ],
            [
                'stat_value' => max($dailyActiveUsers * 45 + $onlineUsers * 90, 0),
            ]
        );

        UsageStatistic::updateOrCreate(
            [
                'stat_type' => UsageStatistic::TYPE_GLOBAL,
                'stat_key' => UsageStatistic::KEY_TOTAL_REQUESTS,
            ],
            [
                'stat_value' => max($dailyActiveUsers * 120 + $onlineUsers * 240, 0),
            ]
        );

        UsageStatistic::updateOrCreate(
            [
                'stat_type' => UsageStatistic::TYPE_GLOBAL,
                'stat_key' => 'dormant_users',
            ],
            [
                'stat_value' => $dormantUsers,
            ]
        );
    }

    /**
     * Create license level statistics.
     */
    private function createLicenseStatistics(): void
    {
        $stats = StatisticsService::getSystemHealth();
        $simulationAccountIds = Account::query()
            ->orderBy('id')
            ->limit(ClientSessionSeeder::TARGET_TOTAL_USERS)
            ->pluck('id');

        $activeLicenses = (int) ($stats['active_licenses'] ?? 0);
        $expiredLicenses = (int) ($stats['expired_licenses'] ?? 0);
        $upgradedLicenses = License::query()
            ->whereIn('used_by', $simulationAccountIds)
            ->where('status', LicenseStatus::UPGRADED->value)
            ->count();

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
