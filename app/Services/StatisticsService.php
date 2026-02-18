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
        $loginCount = EventLog::where('event_type', 'account.login')->count();
        self::updateStatistic('global', 'login_count', $loginCount);

        // Global total usage time (in hours)
        $totalUsageHours = ClientSession::selectRaw(self::usageHoursExpression().' as usage_hours')->value('usage_hours') ?? 0;
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
            ->selectRaw(self::usageHoursExpression().' as usage_hours')
            ->value('usage_hours') ?? 0;

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

    /**
     * Get database system status information
     */
    public static function getDatabaseStatus(): array
    {
        try {
            $connection = DB::connection();
            $pdo = $connection->getPdo();
            $driver = $connection->getDriverName();
            $databaseName = $connection->getDatabaseName();

            // Get database version
            $version = $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);

            // Initialize variables
            $sizeMb = 0;
            $tables = [];
            $maxConnections = 0;
            $threadsConnected = 0;
            $threadsRunning = 0;
            $uptime = 0;
            $uptimeFormatted = 'Unknown';

            // Get database-specific information
            if ($driver === 'sqlite') {
                // SQLite specific queries
                $tableNames = DB::select("SELECT name as table_name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name");

                // Get row count and size for each table
                $tables = [];
                foreach ($tableNames as $tableObj) {
                    $tableName = $tableObj->table_name;
                    $rowCount = DB::table($tableName)->count();

                    // Estimate table size (SQLite doesn't provide per-table size easily)
                    // Use a rough estimate based on row count
                    $sizeMb = $rowCount * 0.001; // Rough estimate

                    $tables[] = (object) [
                        'table_name' => $tableName,
                        'table_rows' => $rowCount,
                        'size_mb' => $sizeMb,
                    ];
                }

                // Get file size for SQLite
                $databasePath = database_path($databaseName);
                if (! file_exists($databasePath)) {
                    $sizeMb = 0;
                } else {
                    $sizeMb = round(filesize($databasePath) / 1024 / 1024, 2);
                }

                // SQLite doesn't have connection pool info
                $maxConnections = 1;
                $threadsConnected = 1;
                $threadsRunning = 1;
                $uptimeFormatted = 'N/A';
            } else {
                // MySQL/MariaDB specific queries
                $sizeResult = DB::select('SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb FROM information_schema.tables WHERE table_schema = ?', [$databaseName]);
                $sizeMb = $sizeResult[0]->size_mb ?? 0;

                $tables = DB::select("SELECT
                    table_name,
                    table_rows,
                    ROUND((data_length + index_length) / 1024 / 1024, 2) AS size_mb
                    FROM information_schema.tables
                    WHERE table_schema = ?
                    AND table_type = 'BASE TABLE'
                    ORDER BY (data_length + index_length) DESC", [$databaseName]);

                // Get connection pool info
                $maxConnectionsResult = $connection->select('SHOW VARIABLES LIKE "max_connections"');
                $maxConnections = (int) ($maxConnectionsResult[0]->Value ?? 0);

                $threadsConnectedResult = $connection->select('SHOW STATUS LIKE "Threads_connected"');
                $threadsConnected = (int) ($threadsConnectedResult[0]->Value ?? 0);

                $threadsRunningResult = $connection->select('SHOW STATUS LIKE "Threads_running"');
                $threadsRunning = (int) ($threadsRunningResult[0]->Value ?? 0);

                // Get uptime
                $uptimeResult = $connection->select('SHOW STATUS LIKE "Uptime"');
                $uptime = (int) ($uptimeResult[0]->Value ?? 0);
                $uptimeFormatted = self::formatUptime($uptime);
            }

            // Get queue jobs count (works for both SQLite and MySQL)
            $queueTable = config('queue.connections.database.table', 'queued_jobs');
            $failedTable = config('queue.failed.table', 'queued_failed_jobs');

            $queuedJobs = DB::table($queueTable)->count();
            $failedJobs = DB::table($failedTable)->count();

            // Get cache status (if using Redis)
            $cacheStatus = [];
            if (config('cache.default') === 'redis') {
                try {
                    $redis = app('redis');
                    $cacheStatus = [
                        'type' => 'Redis',
                        'connected' => true,
                        'db_size' => $redis->dbSize(),
                        'info' => $redis->info(),
                    ];
                } catch (\Exception $e) {
                    $cacheStatus = [
                        'type' => 'Redis',
                        'connected' => false,
                        'error' => $e->getMessage(),
                    ];
                }
            } else {
                $cacheStatus = [
                    'type' => ucfirst(config('cache.default')),
                    'connected' => true,
                ];
            }

            return [
                'database' => [
                    'name' => $databaseName,
                    'version' => $version,
                    'size_mb' => $sizeMb,
                    'connection' => $connection->getName(),
                    'driver' => $driver,
                ],
                'tables' => array_map(function ($table) {
                    return [
                        'name' => $table->table_name,
                        'rows' => (int) ($table->table_rows ?? 0),
                        'size_mb' => (float) ($table->size_mb ?? 0),
                    ];
                }, $tables),
                'connections' => [
                    'max_connections' => $maxConnections,
                    'threads_connected' => $threadsConnected,
                    'threads_running' => $threadsRunning,
                    'usage_percent' => $maxConnections > 0 ? round($threadsConnected / $maxConnections * 100, 2) : 0,
                ],
                'queues' => [
                    'pending_jobs' => $queuedJobs,
                    'failed_jobs' => $failedJobs,
                ],
                'uptime' => [
                    'seconds' => $uptime,
                    'formatted' => $uptimeFormatted,
                ],
                'cache' => $cacheStatus,
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'database' => [
                    'name' => 'Unknown',
                    'version' => 'Unknown',
                    'size_mb' => 0,
                    'connection' => 'Unknown',
                    'driver' => 'Unknown',
                ],
                'tables' => [],
                'connections' => [
                    'max_connections' => 0,
                    'threads_connected' => 0,
                    'threads_running' => 0,
                    'usage_percent' => 0,
                ],
                'queues' => [
                    'pending_jobs' => 0,
                    'failed_jobs' => 0,
                ],
                'uptime' => [
                    'seconds' => 0,
                    'formatted' => 'Unknown',
                ],
                'cache' => [
                    'type' => 'Unknown',
                    'connected' => false,
                ],
            ];
        }
    }

    /**
     * Return a cross-database SQL expression for summing session hours.
     */
    protected static function usageHoursExpression(): string
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            return "SUM((strftime('%s', COALESCE(last_heartbeat_at, datetime('now'))) - strftime('%s', created_at)) / 3600.0)";
        }

        return 'SUM(TIMESTAMPDIFF(HOUR, created_at, COALESCE(last_heartbeat_at, NOW())))';
    }

    /**
     * Format uptime for display
     */
    protected static function formatUptime(int $seconds): string
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        $parts = [];
        if ($days > 0) {
            $parts[] = $days.'d';
        }
        if ($hours > 0) {
            $parts[] = $hours.'h';
        }
        if ($minutes > 0) {
            $parts[] = $minutes.'m';
        }

        return implode(' ', $parts) ?: '0m';
    }
}
