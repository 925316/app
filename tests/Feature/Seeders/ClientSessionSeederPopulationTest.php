<?php

use App\Models\Account;
use App\Models\AccountDevice;
use App\Models\ClientSession;
use App\Services\PackageService;
use Database\Seeders\AccountSeeder;
use Database\Seeders\ClientSessionSeeder;
use Database\Seeders\LicenseSeeder;
use Database\Seeders\PackageReleaseSeeder;

use function Pest\Laravel\seed;

beforeEach(function (): void {
    seed([
        AccountSeeder::class,
        LicenseSeeder::class,
        PackageReleaseSeeder::class,
        ClientSessionSeeder::class,
    ]);
});

it('seeds exactly 214 accounts for simulation population', function (): void {
    expect(Account::query()->count())->toBe(214);
});

it('seeds recent active users within the eligible licensed population', function (): void {
    $recentlyActiveUsers = ClientSession::query()
        ->where('last_heartbeat_at', '>=', now()->subDays(30))
        ->distinct('account_id')
        ->count('account_id');

    $eligibleUsers = Account::query()
        ->whereNotNull('email_verified_at')
        ->where(function ($query): void {
            $query->where('is_suspended', false)
                ->orWhere(function ($query): void {
                    $query->where('is_suspended', true)
                        ->where('suspended_until', '<', now());
                });
        })
        ->whereHas('licenses', function ($query): void {
            $query->where('status', 1)
                ->where('expires_at', '>', now());
        })
        ->count();

    expect($recentlyActiveUsers)->toBeGreaterThanOrEqual(1)
        ->and($recentlyActiveUsers)->toBeLessThanOrEqual($eligibleUsers);
});

it('seeds online users as a subset of daily activity', function (): void {
    $onlineUsers = ClientSession::query()
        ->where('last_heartbeat_at', '>=', now()->subMinutes(5))
        ->distinct('account_id')
        ->count('account_id');

    $dailyActiveUsers = ClientSession::query()
        ->where('last_heartbeat_at', '>=', now()->subDay())
        ->distinct('account_id')
        ->count('account_id');

    expect($onlineUsers)->toBeGreaterThanOrEqual(1)
        ->and($onlineUsers)->toBeLessThanOrEqual($dailyActiveUsers);
});

it('seeds monotonic daily and recent activity windows', function (): void {
    $dailyActiveUsers = ClientSession::query()
        ->where('last_heartbeat_at', '>=', now()->subDay())
        ->distinct('account_id')
        ->count('account_id');

    $recentlyActiveUsers = ClientSession::query()
        ->where('last_heartbeat_at', '>=', now()->subDays(30))
        ->distinct('account_id')
        ->count('account_id');

    $eligibleUsers = Account::query()
        ->whereNotNull('email_verified_at')
        ->where(function ($query): void {
            $query->where('is_suspended', false)
                ->orWhere(function ($query): void {
                    $query->where('is_suspended', true)
                        ->where('suspended_until', '<', now());
                });
        })
        ->whereHas('licenses', function ($query): void {
            $query->where('status', 1)
                ->where('expires_at', '>', now());
        })
        ->count();

    expect($dailyActiveUsers)->toBeGreaterThanOrEqual(1)
        ->and($dailyActiveUsers)->toBeLessThanOrEqual($recentlyActiveUsers)
        ->and($recentlyActiveUsers)->toBeLessThanOrEqual($eligibleUsers);
});

it('seeds exactly 32 resident users with currently bound devices', function (): void {
    $residentUsers = AccountDevice::query()
        ->whereNotNull('bound_at')
        ->whereNull('unbound_at')
        ->distinct('account_id')
        ->count('account_id');

    expect($residentUsers)->toBe(32);
});

it('seeds mixed client versions and keeps older versions among long-inactive sessions', function (): void {
    $distinctVersions = ClientSession::query()
        ->distinct('client_version')
        ->pluck('client_version')
        ->filter(fn (mixed $version): bool => is_string($version) && $version !== '')
        ->values();

    $latestStableVersion = PackageService::getLatestRelease('stable')?->version;

    expect($distinctVersions->count())->toBeGreaterThanOrEqual(2)
        ->and($latestStableVersion)->not->toBeNull()
        ->and($distinctVersions->contains($latestStableVersion))->toBeTrue();

    $dormantOldVersionCount = ClientSession::query()
        ->where(function ($query): void {
            $query->whereNull('last_heartbeat_at')
                ->orWhere('last_heartbeat_at', '<', now()->subDays(30));
        })
        ->where('client_version', '!=', $latestStableVersion)
        ->count();

    expect($dormantOldVersionCount)->toBeGreaterThanOrEqual(1);
});

it('keeps online sessions on latest stable version under force-update policy', function (): void {
    $latestStableVersion = PackageService::getLatestRelease('stable')?->version;

    expect($latestStableVersion)->not->toBeNull();

    $nonLatestOnlineCount = ClientSession::query()
        ->where('last_heartbeat_at', '>=', now()->subMinutes(ClientSessionSeeder::ACTIVE_MINUTES_THRESHOLD))
        ->where('client_version', '!=', $latestStableVersion)
        ->count();

    expect($nonLatestOnlineCount)->toBe(0);
});
