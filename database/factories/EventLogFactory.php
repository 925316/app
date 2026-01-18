<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\EventLog;
use App\Models\License;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
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
            \App\Enums\EventType::SYSTEM_PACKAGE_UPLOADED->value,
        ];

        $eventLevels = [
            0, // info
            1, // warning
            2, // error
        ];

        // Randomly decide if we should use existing account/license or null
        $useAccount = $this->faker->boolean(80);
        $useLicense = $this->faker->boolean(60);
        $useActor = $this->faker->boolean(70);

        return [
            'event_type' => $this->faker->randomElement($eventTypes),
            'event_level' => $this->faker->randomElement($eventLevels),
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
            'details' => $this->generateEventDetails(),
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }

    /**
     * Generate a valid IPv4 address string.
     */
    private function generateValidIpv4(): string
    {
        return sprintf('%d.%d.%d.%d',
            rand(1, 255),
            rand(0, 255),
            rand(0, 255),
            rand(0, 255)
        );
    }

    /**
     * Generate realistic event details based on event type.
     */
    private function generateEventDetails(): array
    {
        $eventType = $this->faker->randomElement([
            \App\Enums\EventType::LICENSE_ACTIVATED->value,
            \App\Enums\EventType::DEVICE_BOUND->value,
            \App\Enums\EventType::DEVICE_UNBOUND->value,
            \App\Enums\EventType::ACCOUNT_LOGIN->value,
            \App\Enums\EventType::LICENSE_SUSPENDED->value,
        ]);

        return match ($eventType) {
            \App\Enums\EventType::LICENSE_ACTIVATED->value => [
                'license_key' => strtoupper($this->faker->bothify('??##-??##-??##-??##')),
                'activation_date' => $this->faker->dateTimeThisYear()->format('Y-m-d H:i:s'),
                'device_count' => $this->faker->numberBetween(1, 5),
                'plan_type' => $this->faker->randomElement(['basic', 'pro', 'enterprise']),
            ],
            \App\Enums\EventType::DEVICE_BOUND->value => [
                'device_id' => $this->faker->uuid(),
                'device_name' => $this->faker->randomElement([
                    'iPhone 13 Pro',
                    'Samsung Galaxy S22',
                    'Windows Laptop',
                    'MacBook Pro',
                    'iPad Pro',
                ]),
                'device_type' => $this->faker->randomElement(['mobile', 'tablet', 'desktop', 'laptop']),
                'os_version' => $this->faker->randomElement(['iOS 16.4', 'Android 13', 'Windows 11', 'macOS 13.2']),
                'binding_time' => $this->faker->dateTimeThisMonth()->format('Y-m-d H:i:s'),
            ],
            \App\Enums\EventType::DEVICE_UNBOUND->value => [
                'device_id' => $this->faker->uuid(),
                'device_name' => $this->faker->randomElement([
                    'iPhone 12',
                    'Android Tablet',
                    'Work Laptop',
                    'Home Desktop',
                ]),
                'unbind_reason' => $this->faker->randomElement([
                    'user_initiated',
                    'device_limit_reached',
                    'license_expired',
                    'admin_action',
                ]),
                'unbind_time' => $this->faker->dateTimeThisMonth()->format('Y-m-d H:i:s'),
            ],
            \App\Enums\EventType::ACCOUNT_LOGIN->value => [
                'attempted_location' => $this->faker->city().', '.$this->faker->country(),
                'usual_location' => $this->faker->city().', '.$this->faker->country(),
                'attempt_time' => $this->faker->dateTimeThisMonth()->format('Y-m-d H:i:s'),
                'user_agent' => $this->faker->userAgent(),
                'action_taken' => $this->faker->randomElement([
                    'blocked',
                    'allowed_with_verification',
                    'notified_user',
                    'no_action',
                ]),
            ],
            \App\Enums\EventType::LICENSE_SUSPENDED->value => [
                'suspension_reason' => $this->faker->randomElement([
                    'multiple_failed_logins',
                    'violation_of_tos',
                    'payment_issue',
                    'admin_discretion',
                    'suspicious_activity',
                ]),
                'suspended_by' => $this->faker->name(),
                'suspension_duration' => $this->faker->randomElement([
                    '24_hours',
                    '7_days',
                    '30_days',
                    'permanent',
                ]),
                'appeal_possible' => $this->faker->boolean(),
                'notification_sent' => $this->faker->boolean(90),
            ],
            default => [
                'note' => $this->faker->sentence(),
                'timestamp' => $this->faker->dateTimeThisYear()->format('Y-m-d H:i:s'),
            ],
        };
    }

    /**
     * State for info level events.
     */
    public function info(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'event_level' => 0,
        ]);
    }

    /**
     * State for warning level events.
     */
    public function warning(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'event_level' => 1,
        ]);
    }

    /**
     * State for error level events.
     */
    public function error(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'event_level' => 2,
        ]);
    }

    /**
     * State for account activated events.
     */
    public function accountActivated(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => \App\Enums\EventType::LICENSE_ACTIVATED->value,
            'event_level' => 0,
        ]);
    }

    /**
     * State for login anomaly events.
     */
    public function loginAnomaly(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => \App\Enums\EventType::ACCOUNT_LOGIN->value,
            'event_level' => 1,
        ]);
    }

    /**
     * State for recent events (last 7 days).
     */
    public function recent(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * State for events with account.
     */
    public function withAccount(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'account_id' => Account::factory(),
        ]);
    }

    /**
     * State for events with license.
     */
    public function withLicense(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'license_id' => License::factory(),
        ]);
    }

    /**
     * State for events with actor.
     */
    public function withActor(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'actor_id' => Account::factory(),
        ]);
    }
}
