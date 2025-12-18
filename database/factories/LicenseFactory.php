<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\License;

class LicenseFactory extends Factory
{
    protected $model = License::class;

    public function definition(): array
    {
        return [
            'key' => $this->generateLicenseKey(),
            'type' => 1, // base license
            'used_by' => null,
            'privilege' => $this->faker->randomElement([1, 2, 3]), // basic, regular, ultimate
            'status' => 0, // unused
            'expires_at' => now()->addYears(99),
            'activated_at' => null,
            'suspended_at' => null,
            'created_from_ip' => $this->faker->ipv4(),
            'notes' => $this->faker->optional(0.3)->sentence(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function generateLicenseKey(): string
    {
        // ^[A-Z0-9]{5}-[0-9A-F]{5}-[A-Z2-7]{5}-[A-Z3-8]{5}-[A-Z0-9]{5}$
        $parts = [];
        $chars = array_merge(range('A', 'Z'), range('0', '9'));

        for ($i = 0; $i < 5; $i++) {
            $part = '';
            for ($j = 0; $j < 5; $j++) {
                $part .= $this->faker->randomElement($chars);
            }
            $parts[] = $part;
        }

        return implode('-', $parts);
    }

    public function active(): static
    {
        return $this->state([
            'status' => 1, // active
            'activated_at' => now()->subDays($this->faker->numberBetween(1, 365)),
        ]);
    }

    public function expired(): static
    {
        return $this->state([
            'status' => 3, // expired
            'expires_at' => now()->subDays(1),
            'activated_at' => now()->subMonths(6),
        ]);
    }

    public function suspended(): static
    {
        return $this->state([
            'status' => 2, // suspended
            'suspended_at' => now()->subDays(3),
            'activated_at' => now()->subMonths(2),
        ]);
    }

    public function upgraded(): static
    {
        return $this->state([
            'status' => 4, // upgraded
            'activated_at' => now()->subMonths(3),
        ]);
    }

    public function revoked(): static
    {
        return $this->state([
            'status' => 5, // revoked
        ]);
    }

    public function tester(): static
    {
        return $this->state([
            'privilege' => 4, // tester
        ]);
    }

    public function staff(): static
    {
        return $this->state([
            'privilege' => 5, // staff
        ]);
    }

    public function upgradeType(): static
    {
        return $this->state([
            'type' => 2, // upgrade license
        ]);
    }
}
