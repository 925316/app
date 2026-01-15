<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
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
            'type' => fake()->randomElement([1, 2]),
            'privilege' => fake()->randomElement([0, 1, 2, 3, 4, 5]),
            'status' => fake()->randomElement([0, 1, 2, 3, 4, 5]),
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
        $segment1 = '';
        $chars1 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        for ($i = 0; $i < 5; $i++) {
            $segment1 .= $chars1[random_int(0, strlen($chars1) - 1)];
        }

        $segment2 = '';
        $chars2 = '0123456789ABCDEF';
        for ($i = 0; $i < 5; $i++) {
            $segment2 .= $chars2[random_int(0, strlen($chars2) - 1)];
        }

        $segment3 = '';
        $chars3 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        for ($i = 0; $i < 5; $i++) {
            $segment3 .= $chars3[random_int(0, strlen($chars3) - 1)];
        }

        $segment4 = '';
        $chars4 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ345678';
        for ($i = 0; $i < 5; $i++) {
            $segment4 .= $chars4[random_int(0, strlen($chars4) - 1)];
        }

        $segment5 = '';
        for ($i = 0; $i < 5; $i++) {
            $segment5 .= $chars1[random_int(0, strlen($chars1) - 1)];
        }

        return "{$segment1}-{$segment2}-{$segment3}-{$segment4}-{$segment5}";
    }

    /**
     * Generate base32-like string with custom character range.
     */
    private function generateBase32(int $length, int $min = 2, int $max = 7): string
    {
        $characters = '';
        for ($i = $min; $i <= $max; $i++) {
            $characters .= strtoupper(base_convert($i, 10, 32));
        }
        
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        return $result;
    }

    /**
     * State for unused licenses.
     */
    public function unused(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 0,
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
        return $this->state(fn (array $attributes) => [
            'status' => 1,
            'activated_at' => fake()->dateTimeBetween('-1 year', '-1 day'),
            'expires_at' => fake()->dateTimeBetween('+1 month', '+2 years'),
            'suspended_at' => null,
        ]);
    }

    /**
     * State for suspended licenses.
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 2,
            'suspended_at' => fake()->dateTimeBetween('-3 months', '-1 day'),
        ]);
    }

    /**
     * State for expired licenses.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 3,
            'expires_at' => fake()->dateTimeBetween('-1 year', '-1 day'),
        ]);
    }

    /**
     * State for upgraded licenses.
     */
    public function upgraded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 4,
        ]);
    }

    /**
     * State for revoked licenses.
     */
    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 5,
        ]);
    }

    /**
     * State for base licenses (type 1).
     */
    public function base(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 1,
        ]);
    }

    /**
     * State for upgrade licenses (type 2).
     */
    public function upgrade(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 2,
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
     * State for basic privilege tier.
     */
    public function basic(): static
    {
        return $this->state(fn (array $attributes) => [
            'privilege' => 1,
        ]);
    }

    /**
     * State for regular privilege tier.
     */
    public function regular(): static
    {
        return $this->state(fn (array $attributes) => [
            'privilege' => 2,
        ]);
    }

    /**
     * State for ultimate privilege tier.
     */
    public function ultimate(): static
    {
        return $this->state(fn (array $attributes) => [
            'privilege' => 3,
        ]);
    }

    /**
     * State for tester privilege tier.
     */
    public function tester(): static
    {
        return $this->state(fn (array $attributes) => [
            'privilege' => 4,
        ]);
    }

    /**
     * State for staff privilege tier.
     */
    public function staff(): static
    {
        return $this->state(fn (array $attributes) => [
            'privilege' => 5,
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
