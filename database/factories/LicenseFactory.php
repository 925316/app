<?php

namespace Database\Factories;

use App\Enums\LicensePrivilege;
use App\Enums\LicenseStatus;
use App\Models\Account;
use App\Models\License;
use App\Services\LicenseService;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\License>
 */
class LicenseFactory extends Factory
{
    /**
     * Coerce mixed datetime input into Carbon.
     */
    private function toCarbon(mixed $value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value->copy();
        }

        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value);
        }

        if (is_string($value) && $value !== '') {
            return Carbon::parse($value);
        }

        return null;
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(LicenseStatus::cases());
        $createdAt = fake()->dateTimeBetween('-365 days', 'now');
        $expiresAt = fake()->dateTimeBetween('now', '+365 days');
        $activatedAt = null;
        $usedBy = null;
        $suspendedAt = null;

        if ($status !== LicenseStatus::UNUSED) {
            $activatedAt = fake()->dateTimeBetween($createdAt, 'now');
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
            'created_at' => $createdAt,
            'updated_at' => $activatedAt ?? $createdAt,
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
            $createdAt = $this->toCarbon($attributes['created_at'] ?? null)
                ?? now()->subDays(fake()->numberBetween(7, 365));
            $nowMinusMinute = now()->subMinute();

            if ($createdAt->greaterThan($nowMinusMinute)) {
                $createdAt = $nowMinusMinute->copy();
            }

            $activationStart = $createdAt->copy()->addMinute();
            if ($activationStart->greaterThan($nowMinusMinute)) {
                $activationStart = $createdAt->copy();
            }

            $activatedAt = fake()->dateTimeBetween($activationStart, $nowMinusMinute);
            $expiresAt = fake()->dateTimeBetween(now()->addDay(), now()->addDays(365));

            return [
                'status' => LicenseStatus::ACTIVE->value,
                'created_at' => $createdAt,
                'activated_at' => $activatedAt,
                'used_by' => $attributes['used_by'] ?? Account::factory(),
                'expires_at' => $expiresAt,
                'suspended_at' => null,
                'updated_at' => $activatedAt,
            ];
        });
    }

    /**
     * State for suspended licenses.
     */
    public function suspended(): static
    {
        return $this->state(function (array $attributes) {
            $createdAt = $this->toCarbon($attributes['created_at'] ?? null)
                ?? now()->subDays(fake()->numberBetween(30, 365));
            $activationEnd = now()->subDays(7);

            if ($createdAt->greaterThan($activationEnd)) {
                $createdAt = $activationEnd->copy();
            }

            $activatedAt = $this->toCarbon($attributes['activated_at'] ?? null)
                ?? fake()->dateTimeBetween($createdAt, $activationEnd);
            $activatedAtCarbon = $this->toCarbon($activatedAt) ?? $createdAt->copy();

            if ($activatedAtCarbon->greaterThan(now())) {
                $activatedAtCarbon = now()->copy();
            }

            $suspendedAt = fake()->dateTimeBetween($activatedAtCarbon, 'now');
            $expiresAt = $attributes['expires_at'] ?? fake()->dateTimeBetween('now', '+365 days');

            return [
                'status' => LicenseStatus::SUSPENDED->value,
                'created_at' => $createdAt,
                'used_by' => $attributes['used_by'] ?? Account::factory(),
                'activated_at' => $activatedAt,
                'suspended_at' => $suspendedAt,
                'expires_at' => $expiresAt,
                'updated_at' => $suspendedAt,
            ];
        });
    }

    /**
     * State for expired licenses.
     */
    public function expired(): static
    {
        return $this->state(function (array $attributes) {
            $createdAtAttribute = $attributes['created_at'] ?? null;
            if ($createdAtAttribute instanceof DateTimeInterface) {
                $createdAt = Carbon::instance($createdAtAttribute);
            } elseif (is_string($createdAtAttribute)) {
                $createdAt = Carbon::parse($createdAtAttribute);
            } else {
                $createdAt = now()->subDays(fake()->numberBetween(120, 360));
            }

            $expiresAt = $createdAt->copy()->addDays(fake()->numberBetween(7, 90));
            if ($expiresAt->greaterThan(now()->subDay())) {
                $expiresAt = now()->subDays(fake()->numberBetween(1, 30));
            }

            if ($expiresAt->lessThanOrEqualTo($createdAt)) {
                $createdAt = $expiresAt->copy()->subDays(fake()->numberBetween(15, 60));
            }

            $activatedAt = $createdAt->copy()->addDays(fake()->numberBetween(1, 7));
            if ($activatedAt->greaterThan($expiresAt)) {
                $activatedAt = $expiresAt->copy()->subDays(1);
            }

            return [
                'status' => LicenseStatus::EXPIRED->value,
                'created_at' => $createdAt,
                'activated_at' => $activatedAt,
                'used_by' => $attributes['used_by'] ?? Account::factory(),
                'expires_at' => $expiresAt,
                'suspended_at' => null,
                'updated_at' => $expiresAt,
            ];
        });
    }

    /**
     * State for upgraded licenses.
     */
    public function upgraded(): static
    {
        return $this->state(function (array $attributes) {
            $createdAt = $this->toCarbon($attributes['created_at'] ?? null)
                ?? now()->subDays(fake()->numberBetween(20, 365));
            $activationEnd = now()->subDays(5);

            if ($createdAt->greaterThan($activationEnd)) {
                $createdAt = $activationEnd->copy();
            }

            $activatedAt = $this->toCarbon($attributes['activated_at'] ?? null)
                ?? fake()->dateTimeBetween($createdAt, $activationEnd);
            $updatedAt = fake()->dateTimeBetween($activatedAt, 'now');

            return [
                'status' => LicenseStatus::UPGRADED->value,
                'created_at' => $createdAt,
                'used_by' => $attributes['used_by'] ?? Account::factory(),
                'activated_at' => $activatedAt,
                'suspended_at' => null,
                'updated_at' => $updatedAt,
            ];
        });
    }

    /**
     * State for revoked licenses.
     */
    public function revoked(): static
    {
        return $this->state(function (array $attributes) {
            $createdAt = $this->toCarbon($attributes['created_at'] ?? null)
                ?? now()->subDays(fake()->numberBetween(20, 365));
            $activationEnd = now()->subDays(5);

            if ($createdAt->greaterThan($activationEnd)) {
                $createdAt = $activationEnd->copy();
            }

            $activatedAt = $this->toCarbon($attributes['activated_at'] ?? null)
                ?? fake()->dateTimeBetween($createdAt, $activationEnd);
            $updatedAt = fake()->dateTimeBetween($activatedAt, 'now');

            return [
                'status' => LicenseStatus::REVOKED->value,
                'created_at' => $createdAt,
                'used_by' => $attributes['used_by'] ?? Account::factory(),
                'activated_at' => $activatedAt,
                'suspended_at' => null,
                'updated_at' => $updatedAt,
            ];
        });
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
