<?php

namespace Database\Factories;

use App\Enums\LicensePrivilege;
use App\Enums\LicenseStatus;
use App\Models\Account;
use App\Models\License;
use App\Services\LicenseService;
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
        $status = fake()->randomElement(LicenseStatus::cases());
        $expiresAt = fake()->dateTimeBetween('now', '+2 years');
        $activatedAt = null;
        $usedBy = null;
        $suspendedAt = null;

        if ($status !== LicenseStatus::UNUSED) {
            $activatedAt = fake()->dateTimeBetween('-1 year', 'now');
            $usedBy = Account::factory();
        }

        if ($status === LicenseStatus::SUSPENDED && $activatedAt) {
            $suspendedAt = fake()->dateTimeBetween($activatedAt, 'now');
        }

        return [
            'key' => $this->generateLicenseKey(),
            'privilege' => fake()->randomElement(LicensePrivilege::cases())->value,
            'status' => $status->value,
            'used_by' => $usedBy,
            'expires_at' => $expiresAt,
            'activated_at' => $activatedAt,
            'suspended_at' => $suspendedAt,
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
        return LicenseService::generateLicenseKey();
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
                'used_by' => $attributes['used_by'] ?? Account::factory(),
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
        return $this->state(function (array $attributes) {
            $activatedAt = $attributes['activated_at'] ?? fake()->dateTimeBetween('-6 months', '-1 day');

            return [
                'status' => LicenseStatus::SUSPENDED->value,
                'used_by' => $attributes['used_by'] ?? Account::factory(),
                'activated_at' => $activatedAt,
                'suspended_at' => fake()->dateTimeBetween($activatedAt, 'now'),
            ];
        });
    }

    /**
     * State for expired licenses.
     */
    public function expired(): static
    {
        return $this->state(function (array $attributes) {
            $expiresAt = fake()->dateTimeBetween('-1 year', '-1 day');
            // activated_at should be before expires_at
            $activatedAt = fake()->dateTimeBetween('-2 years', $expiresAt);

            return [
                'status' => LicenseStatus::EXPIRED->value,
                'activated_at' => $activatedAt,
                'used_by' => $attributes['used_by'] ?? Account::factory(),
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
