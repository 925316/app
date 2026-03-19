<?php

namespace Database\Seeders;

use App\Enums\EventType;
use App\Enums\LicenseStatus;
use App\Models\Account;
use App\Models\AccountDevice;
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
        $accounts = Account::query()->limit(30)->get()->keyBy('id');
        $licenses = License::query()
            ->whereNotNull('used_by')
            ->with('account')
            ->orderBy('created_at')
            ->get();
        $devices = AccountDevice::query()->orderBy('first_seen_at')->get()->groupBy('account_id');

        $this->createLicenseLifecycleEvents($licenses, $devices);
        $this->createDeviceLifecycleEvents($devices, $licenses);
        $this->createAccountSecurityEvents($accounts);
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
                        'suspension_reason' => $this->getRandomSuspensionReason(),
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
                        'revocation_reason' => fake()->randomElement(['policy_violation', 'manual_admin_action', 'payment_issue']),
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
            $account = Account::query()->find($accountId);
            $accountAnchor = $account?->created_at ? $this->safeTime($account->created_at) : null;
            $licenseAnchor = null;
            if ($license) {
                $createdAt = $this->safeTime($license->created_at, $accountAnchor);
                $activatedAt = $license->activated_at ? $this->safeTime($license->activated_at, $createdAt) : $createdAt;
                $licenseAnchor = $activatedAt->greaterThan($createdAt) ? $activatedAt : $createdAt;
            }

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
                            'unbind_reason' => fake()->randomElement(['user_initiated', 'device_replacement', 'security_policy']),
                        ],
                        'created_at' => $unboundAt,
                        'updated_at' => $unboundAt,
                    ]);
                }
            }
        }
    }

    private function createAccountSecurityEvents($accounts): void
    {
        foreach ($accounts as $account) {
            $hasAnomaly = fake()->boolean(35);
            if (! $hasAnomaly) {
                continue;
            }

            $anchor = $this->safeTime($account->last_login_at ?? now()->subDays(fake()->numberBetween(1, 45)), $this->safeTime($account->created_at));
            EventLog::query()->create([
                'event_type' => EventType::ACCOUNT_LOGIN->value,
                'event_level' => EventLog::LEVEL_WARN,
                'account_id' => $account->id,
                'actor_id' => $account->id,
                'ip_address' => $this->generateAnomalousIp(),
                'details' => [
                    'detected_pattern' => $this->getRandomSuspiciousPattern(),
                    'action_taken' => fake()->randomElement(['monitoring_enabled', 'step_up_auth_required', 'notified_user']),
                    'risk_level' => 'medium',
                ],
                'created_at' => $anchor,
                'updated_at' => $anchor,
            ]);

            if (fake()->boolean(20)) {
                $errorAt = $this->safeTime($anchor->copy()->addHours(fake()->numberBetween(1, 12)), $anchor);
                EventLog::query()->create([
                    'event_type' => EventType::ACCOUNT_LOGIN->value,
                    'event_level' => EventLog::LEVEL_ERROR,
                    'account_id' => $account->id,
                    'actor_id' => $account->id,
                    'ip_address' => $this->generateRandomIp(),
                    'details' => [
                        'reason' => 'invalid_credentials',
                        'failed_attempts' => fake()->numberBetween(5, 12),
                        'lockout_triggered' => true,
                    ],
                    'created_at' => $errorAt,
                    'updated_at' => $errorAt,
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

        if ($notBefore && $time->lessThan($notBefore)) {
            $time = $notBefore->copy()->addMinutes(fake()->numberBetween(1, 120));
        }

        if ($time->greaterThan(now())) {
            $time = now()->subMinutes(fake()->numberBetween(1, 60));
        }

        return $time;
    }

    private function generateAnomalousIp(): string
    {
        $anomalousCountries = ['RU', 'CN', 'BR', 'NG', 'TR'];
        $country = $anomalousCountries[array_rand($anomalousCountries)];

        return match ($country) {
            'RU' => '195.'.fake()->numberBetween(10, 240).'.'.fake()->numberBetween(1, 254).'.'.fake()->numberBetween(1, 254),
            'CN' => '120.'.fake()->numberBetween(10, 240).'.'.fake()->numberBetween(1, 254).'.'.fake()->numberBetween(1, 254),
            'BR' => '200.'.fake()->numberBetween(10, 240).'.'.fake()->numberBetween(1, 254).'.'.fake()->numberBetween(1, 254),
            'NG' => '105.'.fake()->numberBetween(10, 240).'.'.fake()->numberBetween(1, 254).'.'.fake()->numberBetween(1, 254),
            'TR' => '88.'.fake()->numberBetween(10, 240).'.'.fake()->numberBetween(1, 254).'.'.fake()->numberBetween(1, 254),
            default => '192.168.'.fake()->numberBetween(1, 254).'.'.fake()->numberBetween(1, 254),
        };
    }

    private function generateRandomIp(): string
    {
        return fake()->numberBetween(1, 255).'.'.fake()->numberBetween(0, 255).'.'.fake()->numberBetween(0, 255).'.'.fake()->numberBetween(1, 254);
    }

    private function getRandomSuspiciousPattern(): string
    {
        $patterns = [
            'multiple_failed_logins',
            'unusual_geo_pattern',
            'brute_force_attempt',
            'credential_stuffing',
            'session_hijacking_attempt',
        ];

        return $patterns[array_rand($patterns)];
    }

    private function getRandomSuspensionReason(): string
    {
        $reasons = [
            'multiple_failed_logins',
            'violation_of_tos',
            'payment_issue',
            'admin_discretion',
            'suspicious_activity',
            'spam_behavior',
        ];

        return $reasons[array_rand($reasons)];
    }
}
