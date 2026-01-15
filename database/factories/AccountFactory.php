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
        $createdAt = $this->faker->dateTimeBetween('-2 years', 'now');
        $suspended = $this->faker->boolean(5); // 5% chance of being suspended

        return [
            'username' => $this->faker->unique()->userName(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'), // Default test password
            'last_login_at' => $this->faker->optional(0.7)->dateTimeBetween($createdAt, 'now'),
            'last_ip_address' => $this->faker->optional()->ipv4(),
            'last_user_agent' => $this->faker->optional()->userAgent(),
            'hwid_reset_count' => $this->faker->numberBetween(0, 5),
            'hwid_last_reset_at' => $this->faker->optional(0.3)->dateTimeBetween($createdAt, 'now'),
            'is_suspended' => $suspended,
            'suspension_reason' => $suspended ? $this->faker->randomElement([
                'Violation of terms of service',
                'Suspicious activity detected',
                'Payment issue',
                'Manual suspension by admin',
                'Multiple failed login attempts'
            ]) : null,
            'suspended_until' => $suspended ? $this->faker->optional(0.5)->dateTimeBetween('now', '+30 days') : null,
            'email_verified_at' => $this->faker->optional(0.8)->dateTimeBetween($createdAt, 'now'),
            'two_factor_secret' => $this->faker->optional(0.2)->text(20),
            'two_factor_recovery_codes' => $this->faker->optional(0.2)->text(100),
            'two_factor_confirmed_at' => $this->faker->optional(0.15)->dateTimeBetween($createdAt, 'now'),
            'remember_token' => Str::random(10),
            'created_at' => $createdAt,
            'updated_at' => $this->faker->dateTimeBetween($createdAt, 'now'),
        ];
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
            'email_verified_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
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
    public function suspended(string $reason = null): static
    {
        return $this->state(fn (array $attributes) => [
            'is_suspended' => true,
            'suspension_reason' => $reason ?? $this->faker->randomElement([
                'Violation of terms of service',
                'Suspicious activity detected',
            ]),
            'suspended_until' => $this->faker->optional(0.7)->dateTimeBetween('now', '+30 days'),
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
            'last_login_at' => now()->subMinutes($this->faker->numberBetween(1, 60)),
            'last_ip_address' => $this->faker->ipv4(),
            'last_user_agent' => $this->faker->userAgent(),
        ]);
    }

    /**
     * Indicate that the account has reset HWID multiple times.
     */
    public function withHwidResets(int $count = 3): static
    {
        return $this->state(fn (array $attributes) => [
            'hwid_reset_count' => $count,
            'hwid_last_reset_at' => now()->subDays($this->faker->numberBetween(1, 30)),
        ]);
    }
}
