<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AccountDevice;

class AccountDeviceFactory extends Factory
{
    protected $model = AccountDevice::class;

    public function definition(): array
    {
        $firstSeen = now()->subDays($this->faker->numberBetween(1, 365));
        $lastSeen = $firstSeen->addDays($this->faker->numberBetween(0, 30));
        $boundAt = $this->faker->optional(0.9)->dateTimeBetween($firstSeen, $lastSeen);

        return [
            'account_id' => null,
            'hwid_hash' => hash('sha256', $this->faker->uuid()),
            'user_agent' => $this->faker->userAgent(),
            'ip_address' => $this->faker->ipv4(),
            'country_code' => $this->faker->randomElement(['US', 'CN', 'JP', 'DE', 'GB', null]),
            'device_fingerprint' => json_encode([
                'screen_resolution' => $this->faker->randomElement(['1920x1080', '2560x1440', '1366x768']),
                'browser_plugins' => $this->faker->words(3),
                'timezone' => $this->faker->timezone(),
                'language' => $this->faker->randomElement(['en-US', 'zh-CN', 'ja-JP']),
                'platform' => $this->faker->randomElement(['Windows', 'macOS', 'Linux', 'Android', 'iOS']),
                'hardware_concurrency' => $this->faker->numberBetween(2, 16),
                'device_memory' => $this->faker->numberBetween(4, 32),
            ]),
            'first_seen_at' => $firstSeen,
            'last_seen_at' => $lastSeen,
            'bound_at' => $boundAt,
            'unbound_at' => null,
            'created_at' => $firstSeen,
            'updated_at' => $lastSeen,
        ];
    }

    public function unbound(): static
    {
        $unboundAt = now()->subDays($this->faker->numberBetween(1, 30));
        $firstSeen = (clone $unboundAt)->subDays($this->faker->numberBetween(1, 90));
        $boundAt = (clone $firstSeen)->addDays($this->faker->numberBetween(0, 7));

        return $this->state([
            'first_seen_at' => $firstSeen,
            'last_seen_at' => $unboundAt,
            'bound_at' => $boundAt,
            'unbound_at' => $unboundAt,
        ]);
    }

    public function suspicious(): static
    {
        return $this->state(fn(array $attributes) => [
            'country_code' => $this->faker->randomElement(['RU', 'BR', 'IN']),
            'ip_address' => $this->faker->ipv4(),
            'device_fingerprint' => json_encode([
                'screen_resolution' => '1024x768',
                'browser_plugins' => ['unknown-plugin'],
                'timezone' => 'UTC',
                'language' => 'en-US',
                'platform' => 'Unknown',
                'hardware_concurrency' => 1,
                'device_memory' => 1,
            ]),
        ]);
    }

    public function withSpecificAccount($accountId): static
    {
        return $this->state([
            'account_id' => $accountId,
        ]);
    }
}
