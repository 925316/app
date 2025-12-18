<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Account;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    protected $model = Account::class;

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
        return [
            'username' => $this->faker->unique()->userName,
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'license_id' => null,
            'last_login_at' => $this->faker->optional(0.7)->dateTimeBetween('-30 days', 'now'),
            'last_ip_address' => $this->faker->optional()->ipv4(),
            'last_user_agent' => $this->faker->optional()->userAgent(),
            'hwid_reset_count' => $this->faker->numberBetween(0, 5),
            'hwid_last_reset_at' => $this->faker->optional(0.2)->dateTimeBetween('-90 days', 'now'),
            'is_suspended' => false,
            'suspension_reason' => null,
            'suspended_until' => null,
            'email_verified_at' => $this->faker->optional(0.8)->dateTimeBetween('-1 year', 'now'),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'remember_token' => null,
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function suspended(string $reason = null, \DateTime $until = null): static
    {
        return $this->state([
            'is_suspended' => true,
            'suspension_reason' => $reason ?? $this->faker->sentence(),
            'suspended_until' => $until ?? $this->faker->dateTimeBetween('+1 week', '+1 month'),
        ]);
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

    public function withLicense($licenseId): static
    {
        return $this->state([
            'license_id' => $licenseId,
        ]);
    }

    /**
     * Indicate that the model does not have two-factor authentication configured.
     */
    public function withoutTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);
    }
}
