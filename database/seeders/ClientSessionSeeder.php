<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountDevice;
use App\Models\ClientSession;
use App\Models\License;
use App\Services\PackageService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ClientSessionSeeder extends Seeder
{
    public const ACTIVE_MINUTES_THRESHOLD = 5;

    public const TARGET_TOTAL_USERS = 214;

    public const TARGET_RESIDENT_BOUND_USERS = 32;

    private const ONLINE_RATIO_OF_DAILY = 0.32;

    private const DAILY_RATIO_OF_RECENT = 0.58;

    private const RECENT_RATIO_OF_ELIGIBLE = 0.72;

    private const LICENSED_COVERAGE_OF_DAILY = 1.15;

    private const MAX_ADDITIONAL_LICENSES_BEYOND_RESIDENT = 24;

    /**
     * @var array<int>
     */
    private array $activityEligibleAccountIds = [];

    /**
     * @var array<int>
     */
    private array $boundNonOnlineAccountIds = [];

    /**
     * @var array{online:int,daily:int,recent:int}
     */
    private array $activityTargets = [
        'online' => 0,
        'daily' => 0,
        'recent' => 0,
    ];

    private string $latestStableVersion = 'unknown';

    /**
     * @var Collection<int, array{version:string,published_at:Carbon}>
     */
    private Collection $stableReleaseTimeline;

    /**
     * @var array<int, string>
     */
    private array $stableClientVersions = [];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ClientSession::query()->delete();
        $this->initializeReleaseTimeline();

        $accounts = $this->ensureSimulationPopulation();
        $this->ensureActiveLicensesForActivity($accounts);
        $this->activityEligibleAccountIds = $this->resolveActivityEligibleAccountIds($accounts);
        $this->activityTargets = $this->resolveActivityTargets(count($this->activityEligibleAccountIds));
        [$onlineAccounts, $todayOfflineAccounts, $recentAccounts, $dormantAccounts] = $this->segmentAccounts($accounts);
        $this->boundNonOnlineAccountIds = $this->selectNonOnlineBoundAccounts($accounts, $onlineAccounts);
        $this->normalizeNonOnlineBindingState($accounts, $onlineAccounts);

        $this->createOnlineSessions($onlineAccounts);
        $this->createTodayOfflineSessions($todayOfflineAccounts);
        $this->createRecentSessions($recentAccounts);
        $this->createDormantSessions($dormantAccounts);
        $this->createBackgroundHistory($accounts);

        $this->displaySessionStats();
    }

    /**
     * @return array<int>
     */
    private function resolveActivityEligibleAccountIds(Collection $accounts): array
    {
        return $accounts
            ->filter(function (Account $account): bool {
                if ($account->isSuspended() || $account->email_verified_at === null) {
                    return false;
                }

                return $account->licenses()
                    ->where('status', 1)
                    ->where('expires_at', '>', now())
                    ->exists();
            })
            ->pluck('id')
            ->values()
            ->all();
    }

    private function ensureActiveLicensesForActivity(Collection $accounts): void
    {
        $licenseEligibleAccounts = $accounts
            ->filter(fn (Account $account): bool => ! $account->isSuspended() && $account->email_verified_at !== null)
            ->values();

        $predictedActivityTargets = $this->resolveActivityTargets($licenseEligibleAccounts->count());
        $licenseUpperBound = min(
            $licenseEligibleAccounts->count(),
            self::TARGET_RESIDENT_BOUND_USERS + self::MAX_ADDITIONAL_LICENSES_BEYOND_RESIDENT
        );
        $targetLicensedCount = min(
            max(
                self::TARGET_RESIDENT_BOUND_USERS,
                (int) ceil($predictedActivityTargets['daily'] * self::LICENSED_COVERAGE_OF_DAILY)
            ),
            $licenseUpperBound
        );
        if ($targetLicensedCount <= 0) {
            return;
        }

        $effectiveLicensedIds = $licenseEligibleAccounts
            ->filter(function (Account $account): bool {
                return $account->licenses()
                    ->where('status', 1)
                    ->where('expires_at', '>', now())
                    ->exists();
            })
            ->pluck('id')
            ->values();

        if ($effectiveLicensedIds->count() >= $targetLicensedCount) {
            return;
        }

        $missingCount = $targetLicensedCount - $effectiveLicensedIds->count();
        $accountsToLicense = $licenseEligibleAccounts
            ->reject(fn (Account $account): bool => $effectiveLicensedIds->contains($account->id))
            ->shuffle()
            ->take($missingCount);

        foreach ($accountsToLicense as $account) {
            License::factory()
                ->active()
                ->create([
                    'used_by' => $account->id,
                    'expires_at' => now()->addDays(fake()->numberBetween(90, 365)),
                    'notes' => 'Seed: Active subscription for activity cohort',
                ]);
        }

        if ($accountsToLicense->count() > 0) {
            $this->command->info('Added '.$accountsToLicense->count().' active licenses to support realistic activity cohorts (target '.$targetLicensedCount.')');
        }
    }

    private function getTargetOnlineUsersCount(): int
    {
        return $this->activityTargets['online'];
    }

    private function getTargetDailyActiveUsersCount(): int
    {
        return $this->activityTargets['daily'];
    }

    private function getTargetRecentActiveUsersCount(): int
    {
        return $this->activityTargets['recent'];
    }

    /**
     * @return array{online:int,daily:int,recent:int}
     */
    private function resolveActivityTargets(int $eligibleCount): array
    {
        if ($eligibleCount <= 0) {
            return [
                'online' => 0,
                'daily' => 0,
                'recent' => 0,
            ];
        }

        $recentFloor = min(self::TARGET_RESIDENT_BOUND_USERS, $eligibleCount);
        $recentTarget = $this->clampInt(
            (int) round($eligibleCount * self::RECENT_RATIO_OF_ELIGIBLE),
            $recentFloor,
            $eligibleCount
        );

        $dailyTarget = $this->clampInt(
            (int) round($recentTarget * self::DAILY_RATIO_OF_RECENT),
            1,
            $recentTarget
        );

        $onlineCeiling = min($dailyTarget, self::TARGET_RESIDENT_BOUND_USERS);
        $onlineTarget = $this->clampInt(
            (int) round($dailyTarget * self::ONLINE_RATIO_OF_DAILY),
            1,
            max($onlineCeiling, 1)
        );

        return [
            'online' => $onlineTarget,
            'daily' => max($onlineTarget, $dailyTarget),
            'recent' => max($dailyTarget, $recentTarget),
        ];
    }

    private function clampInt(int $value, int $min, int $max): int
    {
        return max($min, min($value, $max));
    }

    private function ensureSimulationPopulation(): Collection
    {
        $currentTotalAccounts = Account::query()->count();

        if ($currentTotalAccounts < self::TARGET_TOTAL_USERS) {
            $missingUsers = self::TARGET_TOTAL_USERS - $currentTotalAccounts;
            $createdAccounts = Account::factory()
                ->count($missingUsers)
                ->state(function (): array {
                    $isSuspended = fake()->boolean(8);
                    $isVerified = ! fake()->boolean(16);
                    $createdAt = now()->subDays(fake()->numberBetween(45, 360));
                    $updatedAt = $createdAt->copy()->addDays(fake()->numberBetween(1, 330));

                    if ($updatedAt->greaterThan(now()->subMinute())) {
                        $updatedAt = now()->subMinutes(fake()->numberBetween(1, 120));
                    }

                    $lastLoginAt = fake()->boolean(80)
                        ? $createdAt->copy()->addDays(fake()->numberBetween(1, 330))
                        : null;
                    if ($lastLoginAt && $lastLoginAt->greaterThan(now()->subMinutes(1))) {
                        $lastLoginAt = now()->subMinutes(fake()->numberBetween(1, 60));
                    }
                    if ($lastLoginAt && $lastLoginAt->lessThan($createdAt)) {
                        $lastLoginAt = $createdAt->copy()->addMinutes(fake()->numberBetween(5, 120));
                    }

                    $emailVerifiedAt = $isVerified ? $createdAt->copy()->addDays(fake()->numberBetween(1, 14)) : null;
                    if ($emailVerifiedAt && $emailVerifiedAt->greaterThan($updatedAt)) {
                        $emailVerifiedAt = $updatedAt->copy();
                    }

                    $suspendedUntil = null;
                    if ($isSuspended) {
                        $suspendedUntil = fake()->boolean(55)
                            ? now()->addDays(fake()->numberBetween(2, 30))
                            : now()->subDays(fake()->numberBetween(2, 60));
                    }

                    return [
                        'created_at' => $createdAt,
                        'updated_at' => $updatedAt,
                        'is_suspended' => $isSuspended,
                        'suspension_reason' => $isSuspended ? fake()->randomElement([
                            'billing_hold',
                            'security_review',
                            'too_many_failed_logins',
                            'manual_review_pending',
                        ]) : null,
                        'suspended_until' => $suspendedUntil,
                        'email_verified_at' => $emailVerifiedAt,
                        'last_login_at' => $lastLoginAt,
                    ];
                })
                ->create();

            $this->command->info('Created '.$createdAccounts->count().' additional accounts to reach target population');
        }

        $accounts = Account::query()
            ->orderBy('id')
            ->take(self::TARGET_TOTAL_USERS)
            ->get();

        $this->command->info('Prepared '.$accounts->count().' users for simulation population');

        return $accounts;
    }

    private function segmentAccounts(Collection $accounts): array
    {
        $eligibleForOnline = $accounts
            ->filter(fn (Account $account): bool => in_array($account->id, $this->activityEligibleAccountIds, true))
            ->values();

        $onlineAccounts = $this->takeRandom($eligibleForOnline, $this->getTargetOnlineUsersCount());
        $onlineIds = $onlineAccounts->pluck('id')->all();

        $remaining = $eligibleForOnline
            ->reject(fn (Account $account): bool => in_array($account->id, $onlineIds, true))
            ->values();

        $todayOfflineTarget = max($this->getTargetDailyActiveUsersCount() - $onlineAccounts->count(), 0);
        $todayOfflineAccounts = $this->takeRandom($remaining, $todayOfflineTarget);
        $todayOfflineIds = $todayOfflineAccounts->pluck('id')->all();

        $remaining = $remaining
            ->reject(fn (Account $account): bool => in_array($account->id, $todayOfflineIds, true))
            ->values();

        $recentTarget = max($this->getTargetRecentActiveUsersCount() - $onlineAccounts->count() - $todayOfflineAccounts->count(), 0);
        $recentAccounts = $this->takeRandom($remaining, $recentTarget);
        $recentIds = $recentAccounts->pluck('id')->all();

        $licensedDormantAccounts = $remaining
            ->reject(fn (Account $account): bool => in_array($account->id, $recentIds, true))
            ->values();

        $inactiveEligibleAccounts = $accounts
            ->reject(fn (Account $account): bool => in_array($account->id, $this->activityEligibleAccountIds, true))
            ->values();

        $dormantAccounts = $licensedDormantAccounts
            ->concat($inactiveEligibleAccounts)
            ->values();

        $this->command->table(
            ['Segment', 'Accounts'],
            [
                ['Online now', $onlineAccounts->count()],
                ['Today active but offline', $todayOfflineAccounts->count()],
                ['Recent active (7-30d)', $recentAccounts->count()],
                ['Dormant / long inactive', $dormantAccounts->count()],
                ['Eligible active accounts', count($this->activityEligibleAccountIds)],
                ['Total population', $accounts->count()],
            ]
        );

        return [$onlineAccounts, $todayOfflineAccounts, $recentAccounts, $dormantAccounts];
    }

    /**
     * @return array<int>
     */
    private function selectNonOnlineBoundAccounts(Collection $allAccounts, Collection $onlineAccounts): array
    {
        $onlineIds = $onlineAccounts->pluck('id')->all();
        $nonOnlineAccounts = $allAccounts
            ->reject(fn (Account $account): bool => in_array($account->id, $onlineIds, true))
            ->filter(fn (Account $account): bool => $this->accountHasEffectiveLicense($account))
            ->values();

        $targetBoundCount = max(self::TARGET_RESIDENT_BOUND_USERS - $onlineAccounts->count(), 0);
        $targetBoundCount = min($targetBoundCount, $nonOnlineAccounts->count());
        $selected = $this->takeRandom($nonOnlineAccounts, $targetBoundCount);

        $ids = $selected->pluck('id')->values()->all();

        $this->command->info('Selected '.count($ids).' non-online accounts for bound devices (resident target)');

        return $ids;
    }

    private function normalizeNonOnlineBindingState(Collection $allAccounts, Collection $onlineAccounts): void
    {
        $onlineIds = $onlineAccounts->pluck('id')->all();
        $nonOnlineAccounts = $allAccounts
            ->reject(fn (Account $account): bool => in_array($account->id, $onlineIds, true))
            ->values();

        foreach ($nonOnlineAccounts as $account) {
            $shouldStayBound = $this->shouldNonOnlineAccountBeBound($account);

            if ($shouldStayBound) {
                $this->ensureDevice($account, true, false, 2, 30);

                continue;
            }

            AccountDevice::query()
                ->where('account_id', $account->id)
                ->whereNotNull('bound_at')
                ->whereNull('unbound_at')
                ->get()
                ->each(function (AccountDevice $device): void {
                    $unboundAt = now()->subDays(fake()->numberBetween(2, 30));
                    if ($device->bound_at && $unboundAt->lessThan($device->bound_at)) {
                        $unboundAt = $device->bound_at->copy()->addDays(fake()->numberBetween(1, 14));
                    }

                    $lastSeenAt = $unboundAt->copy()->subHours(fake()->numberBetween(1, 48));
                    if ($device->first_seen_at && $lastSeenAt->lessThan($device->first_seen_at)) {
                        $lastSeenAt = $device->first_seen_at->copy()->addHours(fake()->numberBetween(2, 72));
                    }

                    if ($lastSeenAt->greaterThan($unboundAt)) {
                        $lastSeenAt = $unboundAt->copy()->subMinute();
                    }

                    $device->forceFill([
                        'unbound_at' => $unboundAt,
                        'last_seen_at' => $lastSeenAt,
                    ])->save();
                });
        }

        $boundNonOnlineCount = AccountDevice::query()
            ->whereIn('account_id', $nonOnlineAccounts->pluck('id'))
            ->whereNotNull('bound_at')
            ->whereNull('unbound_at')
            ->distinct('account_id')
            ->count('account_id');

        $this->command->info('Normalized non-online bound device accounts: '.$boundNonOnlineCount);
    }

    private function takeRandom(Collection $accounts, int $count): Collection
    {
        if ($count <= 0) {
            return collect();
        }

        return $accounts->shuffle()->take(min($count, $accounts->count()))->values();
    }

    private function shouldNonOnlineAccountBeBound(Account $account): bool
    {
        return in_array($account->id, $this->boundNonOnlineAccountIds, true);
    }

    private function accountHasEffectiveLicense(Account $account): bool
    {
        return in_array($account->id, $this->activityEligibleAccountIds, true);
    }

    private function ensureDevice(Account $account, bool $mustBeBound, bool $preferUnbound, int $lastSeenDaysMin, int $lastSeenDaysMax): AccountDevice
    {
        $baseQuery = AccountDevice::query()->where('account_id', $account->id);

        if ($mustBeBound) {
            $device = (clone $baseQuery)
                ->whereNotNull('bound_at')
                ->whereNull('unbound_at')
                ->latest('last_seen_at')
                ->first();
        } elseif ($preferUnbound) {
            $device = (clone $baseQuery)
                ->where(function ($query): void {
                    $query->whereNull('bound_at')
                        ->orWhereNotNull('unbound_at');
                })
                ->latest('last_seen_at')
                ->first();

            if (! $device) {
                $device = (clone $baseQuery)->latest('last_seen_at')->first();
            }
        } else {
            $device = (clone $baseQuery)->latest('last_seen_at')->first();
        }

        if ($device) {
            return $device;
        }

        $firstSeen = now()->subDays(fake()->numberBetween(7, 340));
        if ($account->created_at && $firstSeen->lessThan($account->created_at)) {
            $firstSeen = $account->created_at->copy()->addDays(fake()->numberBetween(1, 10));
        }

        $boundAt = $firstSeen->copy()->addDays(fake()->numberBetween(1, 14));
        $lastSeenAt = now()->subDays(fake()->numberBetween($lastSeenDaysMin, $lastSeenDaysMax));

        if ($lastSeenAt->lessThan($boundAt)) {
            $lastSeenAt = $boundAt->copy()->addDays(fake()->numberBetween(1, 14));
        }

        if ($lastSeenAt->greaterThan(now()->subMinute())) {
            $lastSeenAt = now()->subMinutes(fake()->numberBetween(1, 20));
        }

        $isBoundNow = $mustBeBound || ! $preferUnbound;
        $unboundAt = null;
        if (! $isBoundNow) {
            $unboundAt = $boundAt->copy()->addDays(fake()->numberBetween(15, 120));
            if ($unboundAt->greaterThan(now()->subDay())) {
                $unboundAt = now()->subDays(fake()->numberBetween(1, 14));
            }

            if ($unboundAt->lessThanOrEqualTo($boundAt)) {
                $unboundAt = $boundAt->copy()->addDays(fake()->numberBetween(1, 14));
            }

            if ($lastSeenAt->greaterThan($unboundAt)) {
                $lastSeenAt = $unboundAt->copy()->subMinute();
            }

            if ($lastSeenAt->lessThan($boundAt)) {
                $lastSeenAt = $boundAt->copy()->addHours(fake()->numberBetween(1, 24));

                if ($lastSeenAt->greaterThan($unboundAt)) {
                    $lastSeenAt = $unboundAt->copy()->subMinute();
                }
            }
        }

        return AccountDevice::factory()
            ->for($account)
            ->create([
                'first_seen_at' => $firstSeen,
                'bound_at' => $boundAt,
                'unbound_at' => $unboundAt,
                'last_seen_at' => $lastSeenAt,
            ]);
    }

    private function createOnlineSessions(Collection $onlineAccounts): void
    {
        foreach ($onlineAccounts as $account) {
            $device = $this->ensureDevice($account, true, false, 0, 0);
            $startedAt = now()->subMinutes(fake()->numberBetween(10, 360));
            $heartbeatAt = now()->subMinutes(fake()->numberBetween(0, self::ACTIVE_MINUTES_THRESHOLD - 1));

            $this->createSession($account, $device, $startedAt, $heartbeatAt);
        }

        $this->command->info('Created online sessions for concurrent users');
    }

    private function createTodayOfflineSessions(Collection $accounts): void
    {
        foreach ($accounts as $account) {
            if (! $this->accountHasEffectiveLicense($account)) {
                continue;
            }

            $shouldBeBound = $this->shouldNonOnlineAccountBeBound($account);
            $device = $this->ensureDevice($account, $shouldBeBound, ! $shouldBeBound, 0, 1);
            $sessionCount = fake()->numberBetween(1, 3);

            for ($index = 0; $index < $sessionCount; $index++) {
                $minutesSinceStart = max(now()->diffInMinutes(now()->startOfDay()) - 30, 2);
                $createdAt = now()->startOfDay()->addMinutes(fake()->numberBetween(1, $minutesSinceStart));
                $heartbeatAt = $createdAt->copy()->addMinutes(fake()->numberBetween(10, 210));

                if ($heartbeatAt->greaterThan(now()->subMinutes(self::ACTIVE_MINUTES_THRESHOLD + 1))) {
                    $heartbeatAt = now()->subMinutes(fake()->numberBetween(self::ACTIVE_MINUTES_THRESHOLD + 1, 180));
                }

                $this->createSession($account, $device, $createdAt, $heartbeatAt);
            }
        }

        $this->command->info('Created sessions for today-active offline users');
    }

    private function createRecentSessions(Collection $accounts): void
    {
        foreach ($accounts as $account) {
            if (! $this->accountHasEffectiveLicense($account)) {
                continue;
            }

            $shouldBeBound = $this->shouldNonOnlineAccountBeBound($account);
            $device = $this->ensureDevice($account, $shouldBeBound, ! $shouldBeBound, 2, 30);
            $sessionCount = fake()->numberBetween(1, 4);

            for ($index = 0; $index < $sessionCount; $index++) {
                $createdAt = now()->subDays(fake()->numberBetween(2, 30));
                $createdAt = $createdAt->addMinutes(fake()->numberBetween(5, 480));
                $heartbeatAt = $createdAt->copy()->addMinutes(fake()->numberBetween(10, 300));

                if ($heartbeatAt->greaterThan(now()->subMinutes(self::ACTIVE_MINUTES_THRESHOLD + 1))) {
                    $heartbeatAt = now()->subMinutes(fake()->numberBetween(self::ACTIVE_MINUTES_THRESHOLD + 1, 240));
                }

                $this->createSession($account, $device, $createdAt, $heartbeatAt);
            }
        }

        $this->command->info('Created sessions for recent active users');
    }

    private function createDormantSessions(Collection $accounts): void
    {
        foreach ($accounts as $account) {
            $hasEffectiveLicense = $this->accountHasEffectiveLicense($account);
            $shouldBeBound = $hasEffectiveLicense && $this->shouldNonOnlineAccountBeBound($account);
            $device = $this->ensureDevice($account, $shouldBeBound, ! $shouldBeBound, 31, 180);
            $sessionCount = $hasEffectiveLicense ? fake()->numberBetween(0, 2) : 0;

            for ($index = 0; $index < $sessionCount; $index++) {
                $createdAt = now()->subDays(fake()->numberBetween(31, 330));
                $createdAt = $createdAt->addMinutes(fake()->numberBetween(5, 300));

                $heartbeatAt = null;
                if ($hasEffectiveLicense && ! fake()->boolean(40)) {
                    $heartbeatAt = $createdAt->copy()->addMinutes(fake()->numberBetween(10, 180));
                }

                $this->createSession($account, $device, $createdAt, $heartbeatAt);
            }
        }

        $this->command->info('Created dormant/inactive user session traces');
    }

    private function createBackgroundHistory(Collection $accounts): void
    {
        $sampledAccounts = $accounts
            ->filter(fn (Account $account): bool => $this->accountHasEffectiveLicense($account))
            ->shuffle()
            ->take(min(80, count($this->activityEligibleAccountIds)));

        foreach ($sampledAccounts as $account) {
            $shouldBeBound = $this->shouldNonOnlineAccountBeBound($account);
            $device = $this->ensureDevice($account, $shouldBeBound, ! $shouldBeBound, 2, 120);
            $backgroundCount = fake()->numberBetween(1, 2);

            for ($index = 0; $index < $backgroundCount; $index++) {
                $createdAt = now()->subDays(fake()->numberBetween(31, 120));
                $heartbeatAt = $createdAt->copy()->addMinutes(fake()->numberBetween(10, 360));

                if ($heartbeatAt->greaterThan(now()->subMinutes(self::ACTIVE_MINUTES_THRESHOLD + 1))) {
                    $heartbeatAt = now()->subMinutes(fake()->numberBetween(self::ACTIVE_MINUTES_THRESHOLD + 1, 240));
                }

                $this->createSession($account, $device, $createdAt, $heartbeatAt);
            }
        }

        $this->command->info('Created background historical sessions');
    }

    private function createSession(Account $account, AccountDevice $device, Carbon $createdAt, ?Carbon $heartbeatAt): void
    {
        $accountCreatedAt = $account->created_at ? Carbon::parse($account->created_at) : null;
        if ($accountCreatedAt && $createdAt->lessThan($accountCreatedAt)) {
            $createdAt = $accountCreatedAt->copy()->addMinutes(fake()->numberBetween(5, 180));
        }

        if ($createdAt->greaterThan(now()->subMinute())) {
            $createdAt = now()->subMinutes(fake()->numberBetween(2, 60));
        }

        if ($heartbeatAt && $heartbeatAt->lessThan($createdAt)) {
            $heartbeatAt = $createdAt->copy()->addMinutes(fake()->numberBetween(5, 90));
        }

        if ($heartbeatAt && $heartbeatAt->greaterThan(now())) {
            $heartbeatAt = now()->subMinutes(fake()->numberBetween(1, 5));
        }

        $updatedAt = $heartbeatAt ?? $createdAt->copy()->addMinutes(fake()->numberBetween(5, 90));
        if ($updatedAt->lessThan($createdAt)) {
            $updatedAt = $createdAt->copy();
        }

        ClientSession::factory()
            ->forDevice($device)
            ->create([
                'account_id' => $account->id,
                'device_id' => $device->id,
                'client_version' => $this->resolveSessionClientVersion($createdAt, $heartbeatAt),
                'created_at' => $createdAt,
                'last_heartbeat_at' => $heartbeatAt,
                'updated_at' => $updatedAt,
            ]);
    }

    private function initializeReleaseTimeline(): void
    {
        $stableReleases = PackageService::getAllReleases('stable')
            ->filter(fn (mixed $release): bool => is_object($release)
                && is_string($release->version)
                && $release->version !== '')
            ->map(function ($release): array {
                $publishedAt = $release->created_at instanceof Carbon
                    ? $release->created_at->copy()
                    : now()->copy();

                return [
                    'version' => $release->version,
                    'published_at' => $publishedAt,
                ];
            })
            ->sortBy(fn (array $release): int => $release['published_at']->timestamp)
            ->values()
            ->all();

        if ($stableReleases !== []) {
            /** @var Collection<int, array{version:string,published_at:Carbon}> $timeline */
            $timeline = collect($stableReleases);
            $this->stableReleaseTimeline = $timeline;
            $this->latestStableVersion = (string) $timeline->last()['version'];

            return;
        }

        $latestAnyVersion = PackageService::getLatestRelease()?->version;

        if (is_string($latestAnyVersion) && $latestAnyVersion !== '') {
            $versions = $this->expandVersionPoolFromAnchor($latestAnyVersion);
            $this->stableReleaseTimeline = collect($this->materializeSyntheticTimeline($versions));
            $this->latestStableVersion = $versions[0];

            return;
        }

        $versions = $this->buildCalendarFallbackVersionPool();
        $this->stableReleaseTimeline = collect($this->materializeSyntheticTimeline($versions));
        $this->latestStableVersion = $versions[0];
    }

    /**
     * @param  array<int, string>  $versions
     * @return array<int, array{version:string,published_at:Carbon}>
     */
    private function materializeSyntheticTimeline(array $versions): array
    {
        $total = count($versions);

        return array_map(
            fn (string $version, int $index): array => [
                'version' => $version,
                'published_at' => now()->subDays(($total - $index) * 30),
            ],
            $versions,
            array_keys($versions),
        );
    }

    /**
     * @return array<int, string>
     */
    private function expandVersionPoolFromAnchor(string $anchorVersion): array
    {
        if (preg_match('/^(\d{2})\.(\d{1,2})\.(\d+)$/', $anchorVersion, $matches)) {
            $year = (int) $matches[1];
            $month = (int) $matches[2];
            $sequence = max((int) $matches[3], 1);

            return [
                sprintf('%02d.%d.%d', $year, $month, $sequence),
                sprintf('%02d.%d.%d', $year, $month, max($sequence - 1, 1)),
                sprintf('%02d.%d.%d', $year, $month, max($sequence - 3, 1)),
            ];
        }

        if (preg_match('/^(\d+)\.(\d+)\.(\d+)$/', $anchorVersion, $matches)) {
            $major = (int) $matches[1];
            $minor = (int) $matches[2];
            $patch = max((int) $matches[3], 0);

            return [
                sprintf('%d.%d.%d', $major, $minor, $patch),
                sprintf('%d.%d.%d', $major, $minor, max($patch - 1, 0)),
                sprintf('%d.%d.%d', $major, $minor, max($patch - 3, 0)),
            ];
        }

        return [
            $anchorVersion,
            $anchorVersion.'-legacy.1',
            $anchorVersion.'-legacy.2',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function buildCalendarFallbackVersionPool(): array
    {
        $year = (int) now()->format('y');
        $month = (int) now()->format('n');

        return [
            sprintf('%02d.%d.%d', $year, $month, 12),
            sprintf('%02d.%d.%d', $year, $month, 9),
            sprintf('%02d.%d.%d', $year, $month, 5),
        ];
    }

    private function resolveSessionClientVersion(Carbon $createdAt, ?Carbon $heartbeatAt): string
    {
        if ($heartbeatAt !== null && $heartbeatAt->greaterThanOrEqualTo(now()->subMinutes(self::ACTIVE_MINUTES_THRESHOLD))) {
            return $this->latestStableVersion;
        }

        $referenceTime = $heartbeatAt ?? $createdAt;

        $historicalRelease = $this->stableReleaseTimeline
            ->filter(fn (array $release): bool => $release['published_at']->lessThanOrEqualTo($referenceTime))
            ->last();

        if (is_array($historicalRelease) && isset($historicalRelease['version'])) {
            return (string) $historicalRelease['version'];
        }

        $oldestRelease = $this->stableReleaseTimeline->first();

        if (is_array($oldestRelease) && isset($oldestRelease['version'])) {
            return (string) $oldestRelease['version'];
        }

        return $this->latestStableVersion;
    }

    /**
     * Display session statistics.
     */
    private function displaySessionStats(): void
    {
        $this->command->info(str_repeat('-', 50));
        $this->command->info('CLIENT SESSION STATISTICS');
        $this->command->info(str_repeat('-', 50));

        $total = ClientSession::count();
        $active = ClientSession::where('last_heartbeat_at', '>=', now()->subMinutes(self::ACTIVE_MINUTES_THRESHOLD))->count();
        $expired = ClientSession::where('last_heartbeat_at', '<', now()->subMinutes(self::ACTIVE_MINUTES_THRESHOLD))->count();
        $noHeartbeat = ClientSession::whereNull('last_heartbeat_at')->count();

        $this->command->table(
            ['Status', 'Count'],
            [
                ['Active Sessions', $active],
                ['Expired Sessions', $expired],
                ['No Heartbeat Sessions', $noHeartbeat],
                ['Total Sessions', $total],
                ['Target Total Users', self::TARGET_TOTAL_USERS],
                ['Target Daily Active Users', $this->getTargetDailyActiveUsersCount()],
                ['Target Recent Active Users', $this->getTargetRecentActiveUsersCount()],
                ['Target Online Users', $this->getTargetOnlineUsersCount()],
            ]
        );

        $versionStats = ClientSession::selectRaw('client_version, count(*) as count')
            ->groupBy('client_version')
            ->orderByDesc('count')
            ->get();

        if ($versionStats->isNotEmpty()) {
            $this->command->info('');
            $this->command->info('Version distribution:');
            foreach ($versionStats as $stat) {
                $this->command->info("  {$stat->client_version}: {$stat->count}");
            }
        }

        $this->command->info(str_repeat('-', 50));
    }
}
