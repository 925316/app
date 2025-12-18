<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Upgrade;

class UpgradeFactory extends Factory
{
    protected $model = Upgrade::class;

    public function definition(): array
    {
        $createdAt = now()->subDays($this->faker->numberBetween(1, 365));

        return [
            'account_id' => \App\Models\Account::factory(),
            'license_from' => \App\Models\License::factory(),
            'license_to' => \App\Models\License::factory(),
            'notes' => $this->faker->optional(0.5)->sentence(),
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];
    }

    public function withSpecificAccount($accountId): static
    {
        return $this->state([
            'account_id' => $accountId,
        ]);
    }

    public function withSpecificLicenses($fromLicenseId, $toLicenseId): static
    {
        return $this->state([
            'license_from' => $fromLicenseId,
            'license_to' => $toLicenseId,
        ]);
    }

    public function recent(): static
    {
        return $this->state([
            'created_at' => now()->subDays($this->faker->numberBetween(1, 30)),
        ]);
    }

    public function historical(): static
    {
        return $this->state([
            'created_at' => now()->subMonths($this->faker->numberBetween(3, 24)),
        ]);
    }
}