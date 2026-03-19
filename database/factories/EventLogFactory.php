<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\EventLog;
use App\Models\License;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventLog>
 */
class EventLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EventLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $eventTypes = [
            \App\Enums\EventType::ACCOUNT_REGISTERED->value,
            \App\Enums\EventType::ACCOUNT_LOGIN->value,
            \App\Enums\EventType::LICENSE_ACTIVATED->value,
            \App\Enums\EventType::DEVICE_BOUND->value,
            \App\Enums\EventType::DEVICE_UNBOUND->value,
            \App\Enums\EventType::LICENSE_UPGRADED->value,
            \App\Enums\EventType::LICENSE_SUSPENDED->value,
            \App\Enums\EventType::LICENSE_EXPIRED->value,
            \App\Enums\EventType::SYSTEM_PACKAGE_UPLOADED->value,
        ];

        $eventLevels = [
            0, // info
            1, // warning
            2, // error
        ];

        // Randomly decide if we should use existing account/license or null
        $useAccount = fake()->boolean(80);
        $useLicense = fake()->boolean(60);
        $useActor = fake()->boolean(70);

        $eventType = fake()->randomElement($eventTypes);

        return [
            'event_type' => $eventType,
            'event_level' => fake()->randomElement($eventLevels),
            'account_id' => $useAccount && Account::count() > 0
                ? Account::inRandomOrder()->first()->id
                : null,
            'license_id' => $useLicense && License::count() > 0
                ? License::inRandomOrder()->first()->id
                : null,
            'ip_address' => $this->generateValidIpv4(),
            'actor_id' => $useActor && Account::count() > 0
                ? Account::inRandomOrder()->first()->id
                : null,
            'details' => $this->generateEventDetails($eventType),
            'created_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }

    /**
     * Generate a valid IPv4 address string.
     */
    private function generateValidIpv4(): string
    {
        return sprintf('%d.%d.%d.%d',
            fake()->numberBetween(1, 255),
            fake()->numberBetween(0, 255),
            fake()->numberBetween(0, 255),
            fake()->numberBetween(0, 255)
        );
    }

    /**
     * Generate realistic event details based on event type.
     */
    private function generateEventDetails(string $eventType): array
    {
        return match ($eventType) {
            \App\Enums\EventType::LICENSE_ACTIVATED->value => [
                'license_key' => strtoupper(fake()->bothify('??##-??##-??##-??##')),
                'activation_date' => fake()->dateTimeThisYear()->format('Y-m-d H:i:s'),
                'device_count' => fake()->numberBetween(1, 5),
                'plan_type' => fake()->randomElement(['basic', 'pro', 'enterprise']),
            ],
            \App\Enums\EventType::LICENSE_UPGRADED->value => [
                'previous_plan' => fake()->randomElement(['standard', 'upgrade']),
                'new_plan' => fake()->randomElement(['ultimate', 'staff']),
                'reason' => 'tier_upgrade',
            ],
            \App\Enums\EventType::LICENSE_EXPIRED->value => [
                'attempted_action' => 'license_validation',
                'suggested_action' => 'renew_license',
            ],
            \App\Enums\EventType::DEVICE_BOUND->value => [
                'device_id' => fake()->uuid(),
                'device_name' => fake()->randomElement([
                    'iPhone 13 Pro',
                    'Samsung Galaxy S22',
                    'Windows Laptop',
                    'MacBook Pro',
                    'iPad Pro',
                ]),
                'device_type' => fake()->randomElement(['mobile', 'tablet', 'desktop', 'laptop']),
                'os_version' => fake()->randomElement(['iOS 16.4', 'Android 13', 'Windows 11', 'macOS 13.2']),
                'binding_time' => fake()->dateTimeThisMonth()->format('Y-m-d H:i:s'),
            ],
            \App\Enums\EventType::DEVICE_UNBOUND->value => [
                'device_id' => fake()->uuid(),
                'device_name' => fake()->randomElement([
                    'iPhone 12',
                    'Android Tablet',
                    'Work Laptop',
                    'Home Desktop',
                ]),
                'unbind_reason' => fake()->randomElement([
                    'user_initiated',
                    'device_limit_reached',
                    'license_expired',
                    'admin_action',
                ]),
                'unbind_time' => fake()->dateTimeThisMonth()->format('Y-m-d H:i:s'),
            ],
            \App\Enums\EventType::ACCOUNT_LOGIN->value => [
                'attempted_location' => fake()->city().', '.fake()->country(),
                'usual_location' => fake()->city().', '.fake()->country(),
                'attempt_time' => fake()->dateTimeThisMonth()->format('Y-m-d H:i:s'),
                'user_agent' => fake()->userAgent(),
                'action_taken' => fake()->randomElement([
                    'blocked',
                    'allowed_with_verification',
                    'notified_user',
                    'no_action',
                ]),
            ],
            \App\Enums\EventType::LICENSE_SUSPENDED->value => [
                'suspension_reason' => fake()->randomElement([
                    'multiple_failed_logins',
                    'violation_of_tos',
                    'payment_issue',
                    'admin_discretion',
                    'suspicious_activity',
                ]),
                'suspended_by' => fake()->name(),
                'suspension_duration' => fake()->randomElement([
                    '24_hours',
                    '7_days',
                    '30_days',
                    'permanent',
                ]),
                'appeal_possible' => fake()->boolean(),
                'notification_sent' => fake()->boolean(90),
            ],
            default => [
                'note' => fake()->sentence(),
                'timestamp' => fake()->dateTimeThisYear()->format('Y-m-d H:i:s'),
            ],
        };
    }

    /**
     * State for info level events.
     */
    public function info(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_level' => 0,
        ]);
    }

    /**
     * State for warning level events.
     */
    public function warning(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_level' => 1,
        ]);
    }

    /**
     * State for error level events.
     */
    public function error(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_level' => 2,
        ]);
    }

    /**
     * State for account activated events.
     */
    public function accountActivated(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => \App\Enums\EventType::LICENSE_ACTIVATED->value,
            'event_level' => 0,
        ]);
    }

    /**
     * State for login anomaly events.
     */
    public function loginAnomaly(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => \App\Enums\EventType::ACCOUNT_LOGIN->value,
            'event_level' => 1,
        ]);
    }

    /**
     * State for recent events (last 7 days).
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * State for events with account.
     */
    public function withAccount(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_id' => Account::factory(),
        ]);
    }

    /**
     * State for events with license.
     */
    public function withLicense(): static
    {
        return $this->state(fn (array $attributes) => [
            'license_id' => License::factory(),
        ]);
    }

    /**
     * State for events with actor.
     */
    public function withActor(): static
    {
        return $this->state(fn (array $attributes) => [
            'actor_id' => Account::factory(),
        ]);
    }
}
