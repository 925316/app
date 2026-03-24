<?php

namespace Database\Seeders;

use App\Enums\EventType;
use App\Enums\LicenseStatus;
use App\Models\Account;
use App\Models\AccountDevice;
use App\Models\ClientSession;
use App\Models\EventLog;
use App\Models\License;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class EventLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        EventLog::query()->delete();

        $accounts = Account::query()
            ->orderBy('id')
            ->get()
            ->keyBy('id');
        $licenses = License::query()
            ->whereNotNull('used_by')
            ->with('account')
            ->orderBy('created_at')
            ->get();
        $devices = AccountDevice::query()->orderBy('first_seen_at')->get()->groupBy('account_id');
        $sessionsByAccount = ClientSession::query()
            ->orderBy('created_at')
            ->get()
            ->groupBy('account_id');

        $this->createLicenseLifecycleEvents($licenses, $devices);
        $this->createDeviceLifecycleEvents($devices, $licenses);
        $this->createAccountJourneyEvents($accounts, $licenses, $devices, $sessionsByAccount);
        $this->displayEventLogStats();
    }

    private function createLicenseLifecycleEvents($licenses, $devices): void
    {
        foreach ($licenses as $license) {
            $accountId = $license->used_by;
            if (! $accountId) {
                continue;
            }

            $accountCreatedAt = $license->account?->created_at ? $this->safeTime($license->account->created_at) : null;
            $createdAt = $this->safeTime($license->created_at, $accountCreatedAt);
            $activatedAt = $license->activated_at ? $this->safeTime($license->activated_at, $createdAt) : null;

            EventLog::query()->create([
                'event_type' => EventType::LICENSE_CREATED->value,
                'event_level' => EventLog::LEVEL_INFO,
                'account_id' => $accountId,
                'license_id' => $license->id,
                'actor_id' => $accountId,
                'ip_address' => $license->created_from_ip,
                'details' => [
                    'license_key' => $license->key,
                    'privilege' => $license->privilege?->getLabel() ?? 'unknown',
                    'status' => $license->status?->getLabel() ?? 'unknown',
                    'source' => 'seeder',
                ],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            if ($activatedAt) {
                EventLog::query()->create([
                    'event_type' => EventType::LICENSE_ACTIVATED->value,
                    'event_level' => EventLog::LEVEL_INFO,
                    'account_id' => $accountId,
                    'license_id' => $license->id,
                    'actor_id' => $accountId,
                    'details' => [
                        'license_key' => $license->key,
                        'activation_method' => 'seeded_user_activation',
                        'privilege' => $license->privilege?->getLabel() ?? 'unknown',
                    ],
                    'created_at' => $activatedAt,
                    'updated_at' => $activatedAt,
                ]);
            }

            if ($license->status === LicenseStatus::UPGRADED && $activatedAt) {
                $upgradeAt = $this->safeTime($license->updated_at ?? $activatedAt->copy()->addDays(30), $activatedAt);
                EventLog::query()->create([
                    'event_type' => EventType::LICENSE_UPGRADED->value,
                    'event_level' => EventLog::LEVEL_INFO,
                    'account_id' => $accountId,
                    'license_id' => $license->id,
                    'actor_id' => $accountId,
                    'details' => [
                        'previous_privilege' => $license->privilege?->getLabel() ?? 'unknown',
                        'reason' => 'tier_upgrade',
                    ],
                    'created_at' => $upgradeAt,
                    'updated_at' => $upgradeAt,
                ]);
            }

            if ($license->status === LicenseStatus::SUSPENDED && $license->suspended_at) {
                $suspendedAt = $this->safeTime($license->suspended_at, $activatedAt ?? $createdAt);
                EventLog::query()->create([
                    'event_type' => EventType::LICENSE_SUSPENDED->value,
                    'event_level' => EventLog::LEVEL_WARN,
                    'account_id' => $accountId,
                    'license_id' => $license->id,
                    'actor_id' => $accountId,
                    'details' => [
                        'suspension_reason' => $this->pickFromSeed([
                            'multiple_failed_logins',
                            'violation_of_tos',
                            'payment_issue',
                            'admin_discretion',
                            'suspicious_activity',
                            'spam_behavior',
                        ], (int) $license->id),
                    ],
                    'created_at' => $suspendedAt,
                    'updated_at' => $suspendedAt,
                ]);
            }

            if ($license->status === LicenseStatus::EXPIRED && $license->expires_at) {
                $expiredAt = $this->safeTime($license->expires_at, $activatedAt ?? $createdAt);
                EventLog::query()->create([
                    'event_type' => EventType::LICENSE_EXPIRED->value,
                    'event_level' => EventLog::LEVEL_ERROR,
                    'account_id' => $accountId,
                    'license_id' => $license->id,
                    'actor_id' => $accountId,
                    'details' => [
                        'attempted_action' => 'license_validation',
                        'suggested_action' => 'renew_license',
                    ],
                    'created_at' => $expiredAt,
                    'updated_at' => $expiredAt,
                ]);
            }

            if ($license->status === LicenseStatus::REVOKED) {
                $revokedAt = $this->safeTime($license->updated_at ?? now()->subDays(fake()->numberBetween(1, 30)), $activatedAt ?? $createdAt);
                EventLog::query()->create([
                    'event_type' => EventType::LICENSE_REVOKED->value,
                    'event_level' => EventLog::LEVEL_ERROR,
                    'account_id' => $accountId,
                    'license_id' => $license->id,
                    'actor_id' => $accountId,
                    'details' => [
                        'revocation_reason' => $this->pickFromSeed([
                            'policy_violation',
                            'manual_admin_action',
                            'payment_issue',
                        ], (int) $license->id + 7),
                    ],
                    'created_at' => $revokedAt,
                    'updated_at' => $revokedAt,
                ]);
            }
        }
    }

    private function createDeviceLifecycleEvents($devicesByAccount, $licenses): void
    {
        $activeLicenseByAccount = $licenses
            ->filter(fn (License $license) => $license->status === LicenseStatus::ACTIVE)
            ->groupBy('used_by')
            ->map(fn ($group) => $group->sortByDesc('activated_at')->first());

        foreach ($devicesByAccount as $accountId => $devices) {
            $license = $activeLicenseByAccount->get($accountId);
            if (! $license) {
                continue;
            }
            $account = Account::query()->find($accountId);
            $accountAnchor = $account?->created_at ? $this->safeTime($account->created_at) : null;
            $licenseAnchor = null;
            $createdAt = $this->safeTime($license->created_at, $accountAnchor);
            $activatedAt = $license->activated_at ? $this->safeTime($license->activated_at, $createdAt) : $createdAt;
            $licenseAnchor = $activatedAt->greaterThan($createdAt) ? $activatedAt : $createdAt;

            $eventAnchor = $licenseAnchor ?? $accountAnchor;

            foreach ($devices as $device) {
                if ($device->bound_at) {
                    $boundAt = $this->safeTime(
                        $device->bound_at,
                        $eventAnchor ?? $this->safeTime($device->first_seen_at)
                    );
                    if ($eventAnchor && $boundAt->lessThan($eventAnchor)) {
                        $boundAt = $this->safeTime($eventAnchor->copy()->addMinutes(fake()->numberBetween(1, 90)), $eventAnchor);
                    }
                    EventLog::query()->create([
                        'event_type' => EventType::DEVICE_BOUND->value,
                        'event_level' => EventLog::LEVEL_INFO,
                        'account_id' => $accountId,
                        'license_id' => $license?->id,
                        'actor_id' => $accountId,
                        'ip_address' => $device->ip_address,
                        'details' => [
                            'device_id' => $device->id,
                            'hwid_hash' => $device->hwid_hash,
                            'binding_method' => 'automatic',
                        ],
                        'created_at' => $boundAt,
                        'updated_at' => $boundAt,
                    ]);
                }

                if ($device->unbound_at) {
                    $unboundAt = $this->safeTime(
                        $device->unbound_at,
                        $eventAnchor ?? ($device->bound_at ? $this->safeTime($device->bound_at) : $this->safeTime($device->first_seen_at))
                    );
                    if ($eventAnchor && $unboundAt->lessThan($eventAnchor)) {
                        $unboundAt = $this->safeTime($eventAnchor->copy()->addMinutes(fake()->numberBetween(90, 240)), $eventAnchor);
                    }
                    EventLog::query()->create([
                        'event_type' => EventType::DEVICE_UNBOUND->value,
                        'event_level' => EventLog::LEVEL_INFO,
                        'account_id' => $accountId,
                        'license_id' => $license?->id,
                        'actor_id' => $accountId,
                        'ip_address' => $device->ip_address,
                        'details' => [
                            'device_id' => $device->id,
                            'hwid_hash' => $device->hwid_hash,
                            'unbind_reason' => $this->pickFromSeed([
                                'user_initiated',
                                'device_replacement',
                                'security_policy',
                            ], (int) $device->id),
                        ],
                        'created_at' => $unboundAt,
                        'updated_at' => $unboundAt,
                    ]);
                }
            }
        }
    }

    private function createAccountJourneyEvents($accounts, $licenses, $devicesByAccount, $sessionsByAccount): void
    {
        $activeLicenseByAccount = $licenses
            ->filter(fn (License $license) => $license->status === LicenseStatus::ACTIVE)
            ->groupBy('used_by')
            ->map(fn ($group) => $group->sortByDesc('activated_at')->first());

        foreach ($accounts as $account) {
            $accountCreatedAt = $this->safeTime($account->created_at);
            $activeLicense = $activeLicenseByAccount->get($account->id);
            $accountDevices = $devicesByAccount->get($account->id, collect());

            /** @var AccountDevice|null $primaryDevice */
            $primaryDevice = $accountDevices
                ->sortByDesc(fn (AccountDevice $device) => $device->bound_at?->timestamp ?? 0)
                ->first();

            $session = $sessionsByAccount->get($account->id, collect())
                ->sortByDesc(fn (ClientSession $clientSession) => $clientSession->last_heartbeat_at?->timestamp ?? 0)
                ->first();

            $registeredAt = $accountCreatedAt;
            $verifiedAt = $account->email_verified_at
                ? $this->safeTime($account->email_verified_at, $registeredAt)
                : null;

            EventLog::query()->create([
                'event_type' => EventType::ACCOUNT_REGISTERED->value,
                'event_level' => EventLog::LEVEL_INFO,
                'account_id' => $account->id,
                'actor_id' => $account->id,
                'ip_address' => $this->buildDeterministicIpForAccount($account->id),
                'details' => [
                    'flow' => 'user_journey',
                    'step' => 'register',
                ],
                'created_at' => $registeredAt,
                'updated_at' => $registeredAt,
            ]);

            if ($verifiedAt) {
                EventLog::query()->create([
                    'event_type' => EventType::ACCOUNT_EMAIL_VERIFIED->value,
                    'event_level' => EventLog::LEVEL_INFO,
                    'account_id' => $account->id,
                    'actor_id' => $account->id,
                    'details' => [
                        'flow' => 'user_journey',
                        'step' => 'email_verified',
                    ],
                    'created_at' => $verifiedAt,
                    'updated_at' => $verifiedAt,
                ]);
            }

            if (! $activeLicense || ! $primaryDevice) {
                continue;
            }

            $licenseActivatedAt = $activeLicense->activated_at
                ? $this->safeTime($activeLicense->activated_at, $registeredAt)
                : $this->safeTime($registeredAt->copy()->addDays(1), $registeredAt);

            $boundAt = $primaryDevice->bound_at
                ? $this->safeTime($primaryDevice->bound_at, $licenseActivatedAt)
                : $this->safeTime($licenseActivatedAt->copy()->addHours(2), $licenseActivatedAt);

            $clientLoginAt = $session && $session->created_at
                ? $this->safeTime($session->created_at, $boundAt)
                : $this->safeTime($boundAt->copy()->addHours(1), $boundAt);

            $heartbeatAt = $session && $session->last_heartbeat_at
                ? $this->safeTime($session->last_heartbeat_at, $clientLoginAt)
                : $this->safeTime($clientLoginAt->copy()->addMinutes(20), $clientLoginAt);

            $logoutAt = $this->safeTime($heartbeatAt->copy()->addMinutes(fake()->numberBetween(30, 240)), $heartbeatAt);

            EventLog::query()->create([
                'event_type' => EventType::ACCOUNT_LOGIN->value,
                'event_level' => EventLog::LEVEL_INFO,
                'account_id' => $account->id,
                'license_id' => $activeLicense->id,
                'actor_id' => $account->id,
                'ip_address' => $primaryDevice->ip_address,
                'details' => [
                    'flow' => 'user_journey',
                    'step' => 'client_login',
                    'device_id' => $primaryDevice->id,
                    'session_token' => $session?->session_token,
                ],
                'created_at' => $clientLoginAt,
                'updated_at' => $clientLoginAt,
            ]);

            EventLog::query()->create([
                'event_type' => EventType::ACCOUNT_LOGIN->value,
                'event_level' => EventLog::LEVEL_INFO,
                'account_id' => $account->id,
                'license_id' => $activeLicense->id,
                'actor_id' => $account->id,
                'ip_address' => $primaryDevice->ip_address,
                'details' => [
                    'flow' => 'user_journey',
                    'step' => 'heartbeat_check',
                    'device_id' => $primaryDevice->id,
                ],
                'created_at' => $heartbeatAt,
                'updated_at' => $heartbeatAt,
            ]);

            EventLog::query()->create([
                'event_type' => EventType::ACCOUNT_LOGOUT->value,
                'event_level' => EventLog::LEVEL_INFO,
                'account_id' => $account->id,
                'license_id' => $activeLicense->id,
                'actor_id' => $account->id,
                'ip_address' => $primaryDevice->ip_address,
                'details' => [
                    'flow' => 'user_journey',
                    'step' => 'offline',
                    'device_id' => $primaryDevice->id,
                ],
                'created_at' => $logoutAt,
                'updated_at' => $logoutAt,
            ]);

            if ($account->id % 6 === 0) {
                $passwordChangedAt = $this->safeTime($heartbeatAt->copy()->addMinutes(fake()->numberBetween(10, 120)), $heartbeatAt);

                EventLog::query()->create([
                    'event_type' => EventType::ACCOUNT_PROFILE_UPDATED->value,
                    'event_level' => EventLog::LEVEL_INFO,
                    'account_id' => $account->id,
                    'actor_id' => $account->id,
                    'ip_address' => $this->buildDeterministicIpForAccount($account->id),
                    'details' => [
                        'flow' => 'password_maintenance',
                        'step' => 'password_changed',
                    ],
                    'created_at' => $passwordChangedAt,
                    'updated_at' => $passwordChangedAt,
                ]);
            }

            if ($account->hwid_reset_count > 0 || $account->id % 8 === 0) {
                $resetAt = $account->hwid_last_reset_at
                    ? $this->safeTime($account->hwid_last_reset_at, $clientLoginAt)
                    : $this->safeTime($clientLoginAt->copy()->addDays(1), $clientLoginAt);

                EventLog::query()->create([
                    'event_type' => EventType::ACCOUNT_HWID_RESET->value,
                    'event_level' => EventLog::LEVEL_WARN,
                    'account_id' => $account->id,
                    'actor_id' => $account->id,
                    'ip_address' => $this->buildDeterministicIpForAccount($account->id),
                    'details' => [
                        'flow' => 'device_migration',
                        'step' => 'hwid_reset',
                        'reset_count' => max($account->hwid_reset_count, 1),
                    ],
                    'created_at' => $resetAt,
                    'updated_at' => $resetAt,
                ]);

                if ($primaryDevice->unbound_at || $primaryDevice->bound_at) {
                    $deviceUnboundAt = $primaryDevice->unbound_at
                        ? $this->safeTime($primaryDevice->unbound_at, $resetAt)
                        : $this->safeTime($resetAt->copy()->addMinutes(15), $resetAt);

                    EventLog::query()->create([
                        'event_type' => EventType::DEVICE_UNBOUND->value,
                        'event_level' => EventLog::LEVEL_INFO,
                        'account_id' => $account->id,
                        'license_id' => $activeLicense->id,
                        'actor_id' => $account->id,
                        'ip_address' => $primaryDevice->ip_address,
                        'details' => [
                            'flow' => 'device_migration',
                            'step' => 'old_device_unbound',
                            'device_id' => $primaryDevice->id,
                            'unbind_reason' => 'hwid_reset',
                        ],
                        'created_at' => $deviceUnboundAt,
                        'updated_at' => $deviceUnboundAt,
                    ]);

                    $nextDevice = $accountDevices
                        ->filter(fn (AccountDevice $device): bool => $device->id !== $primaryDevice->id)
                        ->sortByDesc(fn (AccountDevice $device): int => $device->bound_at?->timestamp ?? 0)
                        ->first();

                    if ($nextDevice) {
                        $deviceBoundAt = $nextDevice->bound_at
                            ? $this->safeTime($nextDevice->bound_at, $deviceUnboundAt)
                            : $this->safeTime($deviceUnboundAt->copy()->addMinutes(20), $deviceUnboundAt);

                        EventLog::query()->create([
                            'event_type' => EventType::DEVICE_BOUND->value,
                            'event_level' => EventLog::LEVEL_INFO,
                            'account_id' => $account->id,
                            'license_id' => $activeLicense->id,
                            'actor_id' => $account->id,
                            'ip_address' => $nextDevice->ip_address,
                            'details' => [
                                'flow' => 'device_migration',
                                'step' => 'new_device_bound',
                                'device_id' => $nextDevice->id,
                                'binding_method' => 'post_hwid_reset',
                            ],
                            'created_at' => $deviceBoundAt,
                            'updated_at' => $deviceBoundAt,
                        ]);
                    }
                }
            }

            if ($account->is_suspended || $account->suspended_until?->isFuture()) {
                $suspiciousLoginAt = $this->safeTime($logoutAt->copy()->addHours(2), $logoutAt);
                EventLog::query()->create([
                    'event_type' => EventType::ACCOUNT_LOGIN->value,
                    'event_level' => EventLog::LEVEL_WARN,
                    'account_id' => $account->id,
                    'actor_id' => $account->id,
                    'ip_address' => $this->buildAnomalousIpForAccount($account->id),
                    'details' => [
                        'flow' => 'risk_branch',
                        'step' => 'suspended_login_attempt',
                    ],
                    'created_at' => $suspiciousLoginAt,
                    'updated_at' => $suspiciousLoginAt,
                ]);
            }
        }
    }

    private function displayEventLogStats(): void
    {
        $this->command->info(str_repeat('-', 50));
        $this->command->info('EVENT LOG STATISTICS');
        $this->command->info(str_repeat('-', 50));

        $total = EventLog::count();
        $info = EventLog::where('event_level', 0)->count();
        $warning = EventLog::where('event_level', 1)->count();
        $error = EventLog::where('event_level', 2)->count();
        $withAccount = EventLog::whereNotNull('account_id')->count();
        $withLicense = EventLog::whereNotNull('license_id')->count();
        $withActor = EventLog::whereNotNull('actor_id')->count();

        $this->command->info("Total event logs: {$total}");
        $this->command->info("Info level events: {$info}");
        $this->command->info("Warning level events: {$warning}");
        $this->command->info("Error level events: {$error}");
        $this->command->info("Events with account: {$withAccount}");
        $this->command->info("Events with license: {$withLicense}");
        $this->command->info("Events with actor: {$withActor}");

        $eventTypes = EventLog::selectRaw('event_type, count(*) as count')
            ->groupBy('event_type')
            ->orderByDesc('count')
            ->get();

        if ($eventTypes->isNotEmpty()) {
            $this->command->info('');
            $this->command->info('Event type distribution:');
            foreach ($eventTypes as $row) {
                $this->command->info("  {$row->event_type}: {$row->count}");
            }
        }

        $this->command->info(str_repeat('-', 50));
    }

    private function safeTime(?Carbon $candidate, ?Carbon $notBefore = null): Carbon
    {
        $time = $candidate ? $candidate->copy() : now()->subDays(fake()->numberBetween(1, 60));
        $upperBound = now()->subSecond();

        $effectiveNotBefore = $notBefore?->copy();
        if ($effectiveNotBefore && $effectiveNotBefore->greaterThan($upperBound)) {
            $effectiveNotBefore = $upperBound->copy();
        }

        if ($effectiveNotBefore && $time->lessThan($effectiveNotBefore)) {
            $time = $effectiveNotBefore->copy()->addMinutes(fake()->numberBetween(1, 120));
        }

        if ($time->greaterThan(now())) {
            $time = now()->subMinutes(fake()->numberBetween(1, 60));
        }

        if ($effectiveNotBefore && $time->lessThan($effectiveNotBefore)) {
            $time = $effectiveNotBefore->copy();
        }

        return $time;
    }

    private function buildAnomalousIpForAccount(int $accountId): string
    {
        $anomalousCountries = ['RU', 'CN', 'BR', 'NG', 'TR'];
        $country = $anomalousCountries[$accountId % count($anomalousCountries)];

        return match ($country) {
            'RU' => '195.11.'.($accountId % 250 + 1).'.'.($accountId % 200 + 20),
            'CN' => '120.22.'.($accountId % 250 + 1).'.'.($accountId % 200 + 20),
            'BR' => '200.33.'.($accountId % 250 + 1).'.'.($accountId % 200 + 20),
            'NG' => '105.44.'.($accountId % 250 + 1).'.'.($accountId % 200 + 20),
            'TR' => '88.55.'.($accountId % 250 + 1).'.'.($accountId % 200 + 20),
            default => '192.168.10.'.($accountId % 200 + 20),
        };
    }

    private function buildDeterministicIpForAccount(int $accountId): string
    {
        return '172.16.'.($accountId % 200 + 10).'.'.($accountId % 240 + 12);
    }

    /**
     * @param  array<int, string>  $items
     */
    private function pickFromSeed(array $items, int $seed): string
    {
        if ($items === []) {
            return 'unknown';
        }

        $index = abs($seed) % count($items);

        return $items[$index];
    }
}
