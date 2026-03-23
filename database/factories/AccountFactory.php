<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $createdAt = fake()->dateTimeBetween('-365 days', 'now');
        $suspended = fake()->boolean(5); // 5% chance of being suspended
        $suspendedUntil = $suspended ? fake()->optional(0.5)->dateTimeBetween('now', '+30 days') : null;
        $verificationUpperBound = $suspendedUntil ?: 'now';
        $twoFactorChance = $suspendedUntil ? 0.1 : 0.15;

        return [
            'username' => fake()->unique()->regexify('[A-Za-z0-9_]{12}'),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'), // Default test password
            'last_login_at' => fake()->optional(0.7)->dateTimeBetween($createdAt, 'now'),
            'last_ip_address' => fake()->optional() ? $this->generateValidIpv4() : null,
            'last_user_agent' => fake()->optional()->userAgent(),
            'hwid_reset_count' => fake()->numberBetween(0, 5),
            'hwid_last_reset_at' => fake()->optional(0.3)->dateTimeBetween($createdAt, 'now'),
            'is_suspended' => $suspended,
            'suspension_reason' => $suspended ? fake()->randomElement([
                'Violation of terms of service',
                'Suspicious activity detected',
                'Payment issue',
                'Manual suspension by admin',
                'Multiple failed login attempts',
            ]) : null,
            'suspended_until' => $suspendedUntil,
            'email_verified_at' => fake()->dateTimeBetween($createdAt, $verificationUpperBound),
            'two_factor_secret' => fake()->boolean(20) ? encrypt(Str::random(32)) : null,
            'two_factor_recovery_codes' => fake()->boolean(20) ? encrypt(json_encode([Str::random(10), Str::random(10)])) : null,
            'two_factor_confirmed_at' => fake()->optional($twoFactorChance)->dateTimeBetween($createdAt, $verificationUpperBound),
            'remember_token' => Str::random(10),
            'created_at' => $createdAt,
            'updated_at' => fake()->dateTimeBetween($createdAt, 'now'),
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
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model's email address should be verified.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }

    /**
     * Indicate that the account is suspended.
     */
    public function suspended(?string $reason = null): static
    {
        return $this->state(fn (array $attributes) => [
            'is_suspended' => true,
            'suspension_reason' => $reason ?? fake()->randomElement([
                'Violation of terms of service',
                'Suspicious activity detected',
            ]),
            'suspended_until' => fake()->optional(0.7)->dateTimeBetween('now', '+30 days'),
        ]);
    }

    /**
     * Indicate that the account is active (not suspended).
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_suspended' => false,
            'suspension_reason' => null,
            'suspended_until' => null,
        ]);
    }

    /**
     * Indicate that the account has recently logged in.
     */
    public function recentlyActive(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_login_at' => now()->subMinutes(fake()->numberBetween(1, 60)),
            'last_ip_address' => $this->generateValidIpv4(),
            'last_user_agent' => fake()->userAgent(),
        ]);
    }

    /**
     * Indicate that the account has reset HWID multiple times.
     */
    public function withHwidResets(int $count = 3): static
    {
        return $this->state(fn (array $attributes) => [
            'hwid_reset_count' => $count,
            'hwid_last_reset_at' => now()->subDays(fake()->numberBetween(1, 30)),
        ]);
    }
}
