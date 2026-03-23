<?php

use App\Enums\LicenseStatus;
use App\Models\Account;
use App\Models\ClientSession;
use App\Models\EventLog;
use App\Models\License;
use App\Models\UsageStatistic;
use App\Services\StatisticsService;

it('formats usage time and returns zero fallback', function () {
    expect(StatisticsService::formatUsageTime(0))->toBe('0h');
    expect(StatisticsService::formatUsageTime(1.5))->toBe('1h 30m');
    expect(StatisticsService::formatUsageTime(24))->toBe('1d');
});

it('updates and returns global statistics from current data', function () {
    $account = Account::factory()->create();

    EventLog::factory()->create([
        'event_type' => 'account.login',
        'account_id' => $account->id,
    ]);

    License::factory()->create([
        'used_by' => $account->id,
        'status' => LicenseStatus::ACTIVE->value,
        'expires_at' => now()->addDay(),
    ]);

    ClientSession::factory()->create([
        'account_id' => $account->id,
        'created_at' => now()->subHours(2),
        'last_heartbeat_at' => now()->subHour(),
    ]);

    StatisticsService::updateGlobalStatistics();
    $stats = StatisticsService::getGlobalStatistics();

    expect($stats)->toHaveKeys(['login_count', 'total_usage_hours', 'active_licenses', 'total_accounts', 'active_accounts']);
    expect((int) $stats['login_count'])->toBe(1);
    expect((int) $stats['active_licenses'])->toBe(1);
    expect((int) $stats['total_accounts'])->toBeGreaterThanOrEqual(1);
});

it('updates user statistics and strips user key prefix when fetching', function () {
    $account = Account::factory()->create();

    EventLog::factory()->count(2)->create([
        'event_type' => 'account.login',
        'account_id' => $account->id,
    ]);

    License::factory()->create([
        'used_by' => $account->id,
        'status' => LicenseStatus::ACTIVE->value,
        'expires_at' => now()->addYear(),
    ]);

    ClientSession::factory()->create([
        'account_id' => $account->id,
        'created_at' => now()->subHours(3),
        'last_heartbeat_at' => now()->subHours(1),
    ]);

    StatisticsService::updateUserStatistics($account->id);

    $stats = StatisticsService::getUserStatistics($account->id);
    expect($stats)->toHaveKeys(['login_count', 'usage_hours', 'active_licenses']);
    expect((int) $stats['login_count'])->toBe(2);
    expect((int) $stats['active_licenses'])->toBe(1);
});

it('does nothing when updating user statistics for missing account', function () {
    UsageStatistic::query()->delete();

    StatisticsService::updateUserStatistics(999999);

    expect(UsageStatistic::count())->toBe(0);
});

it('returns database status structure for dashboard consumption', function () {
    $status = StatisticsService::getDatabaseStatus();

    expect($status)->toHaveKeys(['database', 'tables', 'connections', 'queues', 'uptime', 'cache']);
    expect($status['database'])->toHaveKeys(['name', 'version', 'size_mb', 'connection', 'driver']);
});
