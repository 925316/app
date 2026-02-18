<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\EventLog;
use App\Models\License;
use Illuminate\Database\Seeder;

class EventLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing accounts and licenses for realistic relationships
        $accounts = Account::take(10)->get();
        $licenses = License::take(10)->get();

        // Create a variety of event logs
        $this->createInfoEvents($accounts, $licenses);
        $this->createWarningEvents($accounts, $licenses);
        $this->createErrorEvents($accounts, $licenses);
        $this->displayEventLogStats();
    }

    /**
     * Create informational events.
     */
    private function createInfoEvents($accounts, $licenses): void
    {
        // Account activation events - correlate with license activation dates
        $activeLicenses = $licenses->where('status', 1)->whereNotNull('activated_at')->whereNotNull('used_by');
        if ($activeLicenses->isNotEmpty()) {
            EventLog::factory()
                ->count(min(15, $activeLicenses->count()))
                ->accountActivated()
                ->sequence(function ($sequence) use ($activeLicenses) {
                    $license = $activeLicenses->values()->get($sequence->index % $activeLicenses->count());
                    $eventDate = $license->activated_at ?? now()->subDays(rand(1, 365));

                    return [
                        'account_id' => $license->used_by,
                        'license_id' => $license->id,
                        'actor_id' => $license->used_by,
                        'created_at' => $eventDate,
                        'details' => [
                            'license_key' => $license->key,
                            'activation_method' => 'user_activation',
                            'device_count' => 1,
                        ],
                    ];
                })
                ->create();
        }

        // Device binding events - correlate with license activation
        if ($activeLicenses->isNotEmpty()) {
            EventLog::factory()
                ->count(min(12, $activeLicenses->count()))
                ->state(['event_type' => \App\Enums\EventType::DEVICE_BOUND->value])
                ->sequence(function ($sequence) use ($activeLicenses) {
                    $license = $activeLicenses->values()->get($sequence->index % $activeLicenses->count());
                    $activationDate = $license->activated_at ?? now()->subDays(rand(1, 365));
                    $bindDate = $activationDate->copy()->addMinutes(rand(1, 1440)); // Within 24 hours of activation

                    return [
                        'account_id' => $license->used_by,
                        'license_id' => $license->id,
                        'created_at' => $bindDate,
                        'details' => [
                            'device_id' => fake()->uuid(),
                            'device_name' => fake()->randomElement([
                                'Windows Desktop',
                                'MacBook Pro',
                                'Ubuntu Server',
                                'iPhone 14',
                                'Android Tablet',
                            ]),
                            'binding_method' => 'automatic',
                        ],
                    ];
                })
                ->create();
        }

        // Device unbinding events - more recent
        if ($activeLicenses->isNotEmpty()) {
            EventLog::factory()
                ->count(min(6, $activeLicenses->count()))
                ->state(['event_type' => \App\Enums\EventType::DEVICE_UNBOUND->value])
                ->sequence(function ($sequence) use ($activeLicenses) {
                    $license = $activeLicenses->values()->get($sequence->index % $activeLicenses->count());
                    $unbindDate = now()->subDays(rand(1, 90)); // Within last 3 months

                    return [
                        'account_id' => $license->used_by,
                        'license_id' => $license->id,
                        'created_at' => $unbindDate,
                        'details' => [
                            'device_id' => fake()->uuid(),
                            'unbind_reason' => fake()->randomElement([
                                'user_initiated',
                                'license_expired',
                                'device_limit_reached',
                            ]),
                        ],
                    ];
                })
                ->create();
        }
    }

    /**
     * Create warning events.
     */
    private function createWarningEvents($accounts, $licenses): void
    {
        // Login anomaly events
        EventLog::factory()
            ->count(8)
            ->loginAnomaly()
            ->sequence(fn ($sequence) => [
                'account_id' => $accounts->isNotEmpty() ? $accounts->random()->id : null,
                'ip_address' => $this->generateAnomalousIp(),
                'created_at' => now()->subDays(rand(0, 30)),
            ])
            ->create();

        // Suspicious activity warnings - logged as account login anomalies
        EventLog::factory()
            ->count(5)
            ->warning()
            ->state(['event_type' => \App\Enums\EventType::ACCOUNT_LOGIN->value])
            ->sequence(fn ($sequence) => [
                'account_id' => $accounts->isNotEmpty() ? $accounts->random()->id : null,
                'details' => [
                    'detected_pattern' => $this->getRandomSuspiciousPattern(),
                    'action_taken' => 'monitoring_enabled',
                    'risk_level' => 'medium',
                ],
                'created_at' => now()->subDays(rand(0, 45)),
            ])
            ->create();
    }

    /**
     * Create error events.
     */
    private function createErrorEvents($accounts, $licenses): void
    {
        // Account suspension events
        EventLog::factory()
            ->count(3)
            ->error()
            ->state(['event_type' => \App\Enums\EventType::LICENSE_SUSPENDED->value])
            ->sequence(fn ($sequence) => [
                'account_id' => $accounts->isNotEmpty() ? $accounts->random()->id : null,
                'actor_id' => $accounts->isNotEmpty() ? $accounts->random()->id : null,
                'details' => [
                    'suspension_reason' => $this->getRandomSuspensionReason(),
                    'suspended_by' => 'System Administrator',
                    'suspension_duration' => '7_days',
                    'appeal_possible' => true,
                ],
                'created_at' => now()->subDays(rand(0, 15)),
            ])
            ->create();

        // License expired during device binding attempts
        EventLog::factory()
            ->count(3)
            ->error()
            ->state(['event_type' => \App\Enums\EventType::LICENSE_EXPIRED->value])
            ->sequence(fn ($sequence) => [
                'account_id' => $accounts->isNotEmpty() ? $accounts->random()->id : null,
                'license_id' => $licenses->isNotEmpty() ? $licenses->random()->id : null,
                'details' => [
                    'attempted_action' => 'device_binding',
                    'suggested_action' => 'renew_license',
                ],
                'created_at' => now()->subDays(rand(0, 7)),
            ])
            ->create();

        // Repeated failed login attempts
        EventLog::factory()
            ->count(4)
            ->error()
            ->state(['event_type' => \App\Enums\EventType::ACCOUNT_LOGIN->value])
            ->sequence(fn ($sequence) => [
                'account_id' => $accounts->isNotEmpty() ? $accounts->random()->id : null,
                'ip_address' => $this->generateRandomIp(),
                'details' => [
                    'reason' => 'invalid_credentials',
                    'failed_attempts' => rand(5, 20),
                    'lockout_triggered' => true,
                ],
                'created_at' => now()->subHours(rand(1, 48)),
            ])
            ->create();
    }

    /**
     * Display event log statistics.
     */
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

        // Show event type distribution
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

    /**
     * Generate an IP address that would trigger an anomaly.
     */
    private function generateAnomalousIp(): string
    {
        $anomalousCountries = ['RU', 'CN', 'BR', 'NG', 'TR'];
        $country = $anomalousCountries[array_rand($anomalousCountries)];

        // Generate realistic IP ranges for different countries
        return match ($country) {
            'RU' => '195.'.rand(10, 240).'.'.rand(1, 254).'.'.rand(1, 254),
            'CN' => '120.'.rand(10, 240).'.'.rand(1, 254).'.'.rand(1, 254),
            'BR' => '200.'.rand(10, 240).'.'.rand(1, 254).'.'.rand(1, 254),
            'NG' => '105.'.rand(10, 240).'.'.rand(1, 254).'.'.rand(1, 254),
            'TR' => '88.'.rand(10, 240).'.'.rand(1, 254).'.'.rand(1, 254),
            default => '192.168.'.rand(1, 254).'.'.rand(1, 254),
        };
    }

    /**
     * Generate a random IP address.
     */
    private function generateRandomIp(): string
    {
        return rand(1, 255).'.'.rand(0, 255).'.'.rand(0, 255).'.'.rand(1, 254);
    }

    /**
     * Get a random suspicious activity pattern.
     */
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

    /**
     * Get a random suspension reason.
     */
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
