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
        return [
            'username' => $this->faker->unique()->userName,
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'),
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
}
