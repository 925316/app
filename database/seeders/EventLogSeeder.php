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
        // Skip seeding if we already have event logs (for production safety)
        if (EventLog::count() > 0 && !app()->environment('local')) {
            $this->command->info('Event logs already exist. Skipping seeding.');
            return;
        }

        // Get existing accounts and licenses for realistic relationships
        $accounts = Account::take(20)->get();
        $licenses = License::take(15)->get();

        // Create a variety of event logs
        $this->createInfoEvents($accounts, $licenses);
        $this->createWarningEvents($accounts, $licenses);
        $this->createErrorEvents($accounts, $licenses);
    }

    /**
     * Create informational events.
     */
    private function createInfoEvents($accounts, $licenses): void
    {
        // Account activation events
        EventLog::factory()
            ->count(50)
            ->accountActivated()
            ->sequence(fn ($sequence) => [
                'account_id' => $accounts->isNotEmpty() ? $accounts->random()->id : null,
                'license_id' => $licenses->isNotEmpty() ? $licenses->random()->id : null,
                'actor_id' => $accounts->isNotEmpty() ? $accounts->random()->id : null,
                'created_at' => now()->subDays(rand(0, 180)),
            ])
            ->create();

        // Device binding events
        EventLog::factory()
            ->count(30)
            ->state(['event_type' => EventLog::TYPE_DEVICE_BOUND])
            ->sequence(fn ($sequence) => [
                'account_id' => $accounts->isNotEmpty() ? $accounts->random()->id : null,
                'license_id' => $licenses->isNotEmpty() ? $licenses->random()->id : null,
                'created_at' => now()->subDays(rand(0, 90)),
            ])
            ->create();

        // Device unbinding events
        EventLog::factory()
            ->count(20)
            ->state(['event_type' => EventLog::TYPE_DEVICE_UNBOUND])
            ->sequence(fn ($sequence) => [
                'account_id' => $accounts->isNotEmpty() ? $accounts->random()->id : null,
                'license_id' => $licenses->isNotEmpty() ? $licenses->random()->id : null,
                'created_at' => now()->subDays(rand(0, 60)),
            ])
            ->create();
    }

    /**
     * Create warning events.
     */
    private function createWarningEvents($accounts, $licenses): void
    {
        // Login anomaly events
        EventLog::factory()
            ->count(25)
            ->loginAnomaly()
            ->sequence(fn ($sequence) => [
                'account_id' => $accounts->isNotEmpty() ? $accounts->random()->id : null,
                'ip_address' => $this->generateAnomalousIp(),
                'created_at' => now()->subDays(rand(0, 30)),
            ])
            ->create();

        // Suspicious activity warnings
        EventLog::factory()
            ->count(15)
            ->warning()
            ->state(['event_type' => 'security.suspicious_activity'])
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
            ->count(10)
            ->error()
            ->state(['event_type' => EventLog::TYPE_ACCOUNT_SUSPENDED])
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

        // License validation errors
        EventLog::factory()
            ->count(8)
            ->error()
            ->state(['event_type' => 'license.validation_failed'])
            ->sequence(fn ($sequence) => [
                'account_id' => $accounts->isNotEmpty() ? $accounts->random()->id : null,
                'license_id' => $licenses->isNotEmpty() ? $licenses->random()->id : null,
                'details' => [
                    'error_code' => 'LICENSE_EXPIRED',
                    'attempted_action' => 'device_binding',
                    'suggested_action' => 'renew_license',
                ],
                'created_at' => now()->subDays(rand(0, 7)),
            ])
            ->create();

        // API rate limit errors
        EventLog::factory()
            ->count(12)
            ->error()
            ->state(['event_type' => 'api.rate_limit_exceeded'])
            ->sequence(fn ($sequence) => [
                'account_id' => $accounts->isNotEmpty() ? $accounts->random()->id : null,
                'ip_address' => $this->generateRandomIp(),
                'details' => [
                    'endpoint' => '/api/v1/devices/bind',
                    'limit' => '100 requests per hour',
                    'retry_after' => '3600 seconds',
                ],
                'created_at' => now()->subHours(rand(1, 48)),
            ])
            ->create();
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
            'RU' => '195.' . rand(10, 240) . '.' . rand(1, 254) . '.' . rand(1, 254),
            'CN' => '120.' . rand(10, 240) . '.' . rand(1, 254) . '.' . rand(1, 254),
            'BR' => '200.' . rand(10, 240) . '.' . rand(1, 254) . '.' . rand(1, 254),
            'NG' => '105.' . rand(10, 240) . '.' . rand(1, 254) . '.' . rand(1, 254),
            'TR' => '88.' . rand(10, 240) . '.' . rand(1, 254) . '.' . rand(1, 254),
            default => '192.168.' . rand(1, 254) . '.' . rand(1, 254),
        };
    }

    /**
     * Generate a random IP address.
     */
    private function generateRandomIp(): string
    {
        return rand(1, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(1, 254);
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