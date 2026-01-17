<?php

namespace App\Services;

use App\Models\Account;
use App\Models\ClientSession;
use App\Models\EventLog;
use App\Models\License;
use App\Models\UsageStatistic;
use Illuminate\Support\Facades\DB;

class StatisticsService
{
    /**
     * Update global statistics
     */
    public static function updateGlobalStatistics(): void
    {
        // Global login count
        self::updateStatistic('global', 'login_count', Account::count());

        // Global total usage time (in hours)
        $totalUsageHours = ClientSession::sum(DB::raw('TIMESTAMPDIFF(HOUR, created_at, COALESCE(last_heartbeat_at, NOW()))'));
        self::updateStatistic('global', 'total_usage_hours', $totalUsageHours);

        // Active licenses count
        self::updateStatistic('global', 'active_licenses', License::active()->count());

        // Total accounts
        self::updateStatistic('global', 'total_accounts', Account::count());

        // Active accounts (logged in last 30 days)
        $activeAccounts = Account::where('last_login_at', '>=', now()->subDays(30))->count();
        self::updateStatistic('global', 'active_accounts', $activeAccounts);
    }

    /**
     * Update user-specific statistics
     */
    public static function updateUserStatistics(int $accountId): void
    {
        $account = Account::find($accountId);
        if (! $account) {
            return;
        }

        // User login count (approximation)
        $loginCount = EventLog::where('account_id', $accountId)
            ->where('event_type', 'account.login')
            ->count();

        self::updateStatistic('user', "user_{$accountId}_login_count", $loginCount);

        // User usage time (in hours)
        $usageHours = ClientSession::where('account_id', $accountId)
            ->sum(DB::raw('TIMESTAMPDIFF(HOUR, created_at, COALESCE(last_heartbeat_at, NOW()))'));

        self::updateStatistic('user', "user_{$accountId}_usage_hours", $usageHours);

        // User active licenses
        $activeLicenses = License::where('used_by', $accountId)
            ->where('status', \App\Enums\LicenseStatus::ACTIVE->value)
            ->count();

        self::updateStatistic('user', "user_{$accountId}_active_licenses", $activeLicenses);
    }

    /**
     * Update license statistics
     */
    public static function updateLicenseStatistics(int $licenseId): void
    {
        $license = License::find($licenseId);
        if (! $license) {
            return;
        }

        // License activation count
        $activationCount = EventLog::where('license_id', $licenseId)
            ->where('event_type', 'license.activated')
            ->count();

        self::updateStatistic('license', "license_{$licenseId}_activations", $activationCount);
    }

    /**
     * Update or create a statistic
     */
    protected static function updateStatistic(string $category, string $key, float $value): void
    {
        UsageStatistic::updateOrCreate(
            [
                'stat_type' => self::getStatTypeValue($category),
                'stat_key' => $key,
            ],
            ['stat_value' => $value]
        );
    }

    /**
     * Get statistic type value
     */
    protected static function getStatTypeValue(string $category): int
    {
        return match ($category) {
            'global' => 0,
            'user' => 1,
            'license' => 2,
            'server' => 3,
            default => 0,
        };
    }

    /**
     * Get global statistics
     */
    public static function getGlobalStatistics(): array
    {
        $stats = UsageStatistic::where('stat_type', 0)->get();

        $result = [];
        foreach ($stats as $stat) {
            $result[$stat->stat_key] = $stat->stat_value;
        }

        return $result;
    }

    /**
     * Get user statistics for a specific user
     */
    public static function getUserStatistics(int $accountId): array
    {
        $stats = UsageStatistic::where('stat_type', 1)
            ->where('stat_key', 'LIKE', "user_{$accountId}_%")
            ->get();

        $result = [];
        foreach ($stats as $stat) {
            $key = str_replace("user_{$accountId}_", '', $stat->stat_key);
            $result[$key] = $stat->stat_value;
        }

        return $result;
    }

    /**
     * Get license statistics for a specific license
     */
    public static function getLicenseStatistics(int $licenseId): array
    {
        $stats = UsageStatistic::where('stat_type', 2)
            ->where('stat_key', 'LIKE', "license_{$licenseId}_%")
            ->get();

        $result = [];
        foreach ($stats as $stat) {
            $key = str_replace("license_{$licenseId}_", '', $stat->stat_key);
            $result[$key] = $stat->stat_value;
        }

        return $result;
    }

    /**
     * Format usage time for display
     */
    public static function formatUsageTime(float $hours): string
    {
        $years = floor($hours / (24 * 365));
        $remainingHours = $hours % (24 * 365);
        $months = floor($remainingHours / (24 * 30));
        $remainingHours = $remainingHours % (24 * 30);
        $days = floor($remainingHours / 24);
        $remainingHours = $remainingHours % 24;
        $hoursPart = floor($remainingHours);
        $minutes = floor(($remainingHours - $hoursPart) * 60);

        $parts = [];
        if ($years > 0) {
            $parts[] = $years.'y';
        }
        if ($months > 0) {
            $parts[] = $months.'m';
        }
        if ($days > 0) {
            $parts[] = $days.'d';
        }
        if ($hoursPart > 0) {
            $parts[] = $hoursPart.'h';
        }
        if ($minutes > 0) {
            $parts[] = $minutes.'m';
        }

        return implode(' ', $parts);
    }

    /**
     * Get recent activity statistics
     */
    public static function getRecentActivity(int $days = 7): array
    {
        $startDate = now()->subDays($days);

        return [
            'new_accounts' => Account::where('created_at', '>=', $startDate)->count(),
            'new_licenses' => License::where('created_at', '>=', $startDate)->count(),
            'active_sessions' => ClientSession::where('last_heartbeat_at', '>=', $startDate)->count(),
            'login_events' => EventLog::where('event_type', 'account.login')
                ->where('created_at', '>=', $startDate)
                ->count(),
        ];
    }

    /**
     * Get system health statistics
     */
    public static function getSystemHealth(): array
    {
        return [
            'total_accounts' => Account::count(),
            'active_licenses' => License::active()->count(),
            'suspended_accounts' => Account::suspended()->count(),
            'expired_licenses' => License::expired()->count(),
            'unverified_accounts' => Account::unverified()->count(),
        ];
    }
}
