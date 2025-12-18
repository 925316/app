<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\IpRateLimit;

class IpRateLimitFactory extends Factory
{
    protected $model = IpRateLimit::class;

    public function definition(): array
    {
        $createdAt = now()->subHours($this->faker->numberBetween(1, 48));
        $lastRequestAt = (clone $createdAt)->addHours($this->faker->numberBetween(0, 24));
        
        return [
            'ip_address' => $this->faker->ipv4(),
            'endpoint' => $this->faker->randomElement([
                '/api/auth/login',
                '/api/license/activate',
                '/api/download/package',
                '/api/account/register',
                '/api/device/bind',
                '/api/check/update',
            ]),
            'request_count' => $this->faker->numberBetween(1, 80),
            'last_request_at' => $lastRequestAt,
            'is_blocked' => false,
            'blocked_until' => null,
            'block_reason' => null,
            'created_at' => $createdAt,
            'updated_at' => $lastRequestAt,
        ];
    }

    public function blocked(): static
    {
        $blockedUntil = now()->addHours($this->faker->numberBetween(1, 24));
        
        return $this->state([
            'is_blocked' => true,
            'blocked_until' => $blockedUntil,
            'block_reason' => $this->faker->randomElement([
                'Too many requests',
                'Suspicious activity',
                'Multiple failed logins',
                'Potential brute force attack',
            ]),
            'request_count' => $this->faker->numberBetween(100, 1000),
        ]);
    }

    public function recentlyBlocked(): static
    {
        return $this->state([
            'is_blocked' => true,
            'blocked_until' => now()->addMinutes($this->faker->numberBetween(5, 60)),
            'block_reason' => 'Rate limit exceeded',
            'request_count' => 120,
        ]);
    }

    public function expiredBlock(): static
    {
        return $this->state([
            'is_blocked' => true,
            'blocked_until' => now()->subHours($this->faker->numberBetween(1, 12)),
            'block_reason' => 'Previous violation',
            'request_count' => 150,
        ]);
    }

    public function nearThreshold(): static
    {
        return $this->state([
            'request_count' => $this->faker->numberBetween(80, 95),
            'last_request_at' => now()->subMinutes($this->faker->numberBetween(1, 5)),
        ]);
    }

    public function oldRecord(): static
    {
        $createdAt = now()->subHours($this->faker->numberBetween(25, 72));
        $lastRequestAt = (clone $createdAt)->addHours($this->faker->numberBetween(1, 24));
        
        return $this->state([
            'request_count' => $this->faker->numberBetween(1, 30),
            'last_request_at' => $lastRequestAt,
            'created_at' => $createdAt,
            'updated_at' => $lastRequestAt,
        ]);
    }
}