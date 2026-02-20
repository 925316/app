<?php

namespace Database\Factories;

use App\Enums\LicensePrivilege;
use App\Enums\LicenseStatus;
use App\Models\License;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\License>
 */
class LicenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => $this->generateLicenseKey(),
            'privilege' => fake()->randomElement(LicensePrivilege::cases())->value,
            'status' => fake()->randomElement(LicenseStatus::cases())->value,
            'used_by' => null,
            'expires_at' => fake()->dateTimeBetween('now', '+2 years'),
            'activated_at' => fake()->optional(0.7)->dateTimeBetween('-1 year', 'now'),
            'suspended_at' => fake()->optional(0.1)->dateTimeBetween('-6 months', 'now'),
            'created_from_ip' => fake()->ipv4(),
            'notes' => fake()->optional(0.3)->text(200),
        ];
    }

    /**
     * Generate a valid license key matching the regex pattern.
     * Pattern: '^[A-Z0-9]{5}-[0-9A-F]{5}-[A-Z2-7]{5}-[A-Z3-8]{5}-[A-Z0-9]{5}$'
     */
    private function generateLicenseKey(): string
    {
        // Segment 1: A-Z0-9 (5 chars)
        $segment1 = '';
        $chars1 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        for ($i = 0; $i < 5; $i++) {
            $segment1 .= $chars1[random_int(0, strlen($chars1) - 1)];
        }

        // Segment 2: 0-9A-F (5 chars)
        $segment2 = '';
        $chars2 = '0123456789ABCDEF';
        for ($i = 0; $i < 5; $i++) {
            $segment2 .= $chars2[random_int(0, strlen($chars2) - 1)];
        }

        // Segment 3: A-Z2-7 (5 chars)
        $segment3 = '';
        $chars3 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        for ($i = 0; $i < 5; $i++) {
            $segment3 .= $chars3[random_int(0, strlen($chars3) - 1)];
        }

        // Segment 4: A-Z3-8 (5 chars)
        $segment4 = '';
        $chars4 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ345678';
        for ($i = 0; $i < 5; $i++) {
            $segment4 .= $chars4[random_int(0, strlen($chars4) - 1)];
        }

        // Segment 5: A-Z0-9 (5 chars)
        $segment5 = '';
        for ($i = 0; $i < 5; $i++) {
            $segment5 .= $chars1[random_int(0, strlen($chars1) - 1)];
        }

        return "{$segment1}-{$segment2}-{$segment3}-{$segment4}-{$segment5}";
    }

    /**
     * State for unused licenses.
     */
    public function unused(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LicenseStatus::UNUSED->value,
            'used_by' => null,
            'activated_at' => null,
            'suspended_at' => null,
        ]);
    }

    /**
     * State for active licenses.
     */
    public function active(): static
    {
        return $this->state(function (array $attributes) {
            // Activated between 1-365 days ago
            $activatedAt = fake()->dateTimeBetween('-365 days', '-1 day');

            // Expires between tomorrow and 2 years from now (always in the future)
            $minExpiry = now()->addDay();
            $maxExpiry = now()->addYears(2);

            $expiresAt = fake()->dateTimeBetween($minExpiry, $maxExpiry);

            return [
                'status' => LicenseStatus::ACTIVE->value,
                'activated_at' => $activatedAt,
                'expires_at' => $expiresAt,
                'suspended_at' => null,
            ];
        });
    }

    /**
     * State for suspended licenses.
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LicenseStatus::SUSPENDED->value,
            'suspended_at' => fake()->dateTimeBetween('-3 months', '-1 day'),
        ]);
    }

    /**
     * State for expired licenses.
     */
    public function expired(): static
    {
        return $this->state(function (array $attributes) {
            $expiresAt = fake()->dateTimeBetween('-1 year', '-1 day');
            // activated_at should be before expires_at
            $activatedAt = fake()->dateTimeBetween($expiresAt->format('Y-m-d H:i:s'), '+1 year');

            return [
                'status' => LicenseStatus::EXPIRED->value,
                'activated_at' => $activatedAt,
                'expires_at' => $expiresAt,
            ];
        });
    }

    /**
     * State for upgraded licenses.
     */
    public function upgraded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LicenseStatus::UPGRADED->value,
        ]);
    }

    /**
     * State for revoked licenses.
     */
    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LicenseStatus::REVOKED->value,
        ]);
    }

    /**
     * State for specific privilege tiers.
     */
    public function privilege(int $privilege): static
    {
        return $this->state(fn (array $attributes) => [
            'privilege' => $privilege,
        ]);
    }

    /**
     * State for standard privilege tier.
     */
    public function standard(): static
    {
        return $this->state(fn (array $attributes) => [
            'privilege' => LicensePrivilege::STANDARD->value,
        ]);
    }

    /**
     * State for upgrade privilege tier.
     */
    public function upgrade(): static
    {
        return $this->state(fn (array $attributes) => [
            'privilege' => LicensePrivilege::UPGRADE->value,
        ]);
    }

    /**
     * State for ultimate privilege tier.
     */
    public function ultimate(): static
    {
        return $this->state(fn (array $attributes) => [
            'privilege' => LicensePrivilege::ULTIMATE->value,
        ]);
    }

    /**
     * State for tester privilege tier.
     */
    public function tester(): static
    {
        return $this->state(fn (array $attributes) => [
            'privilege' => LicensePrivilege::TESTER->value,
        ]);
    }

    /**
     * State for staff privilege tier.
     */
    public function staff(): static
    {
        return $this->state(fn (array $attributes) => [
            'privilege' => LicensePrivilege::STAFF->value,
        ]);
    }

    /**
     * State for assigned licenses (with used_by).
     */
    public function assigned($accountId = null): static
    {
        return $this->state(fn (array $attributes) => [
            'used_by' => $accountId ?? fake()->numberBetween(1, 100),
        ]);
    }
}
