<?php

namespace Database\Factories;

use App\Models\UsageStatistic;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class UsageStatisticFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statTypes = [
            UsageStatistic::TYPE_GLOBAL,
            UsageStatistic::TYPE_USER,
            UsageStatistic::TYPE_LICENSE,
            UsageStatistic::TYPE_SERVER,
        ];

        $statKeys = [
            UsageStatistic::KEY_LOGIN_COUNT,
            UsageStatistic::KEY_USAGE_TIME,
            UsageStatistic::KEY_TOTAL_REQUESTS,
            UsageStatistic::KEY_ACTIVE_SESSIONS,
            'api_calls',
            'storage_used',
            'memory_usage',
            'cpu_usage',
            'disk_io',
            'network_traffic',
        ];

        $statType = $this->faker->randomElement($statTypes);
        $statKey = $this->faker->randomElement($statKeys);

        // Generate appropriate values based on the stat key
        $statValue = match ($statKey) {
            UsageStatistic::KEY_LOGIN_COUNT => $this->faker->numberBetween(100, 1000000),
            UsageStatistic::KEY_USAGE_TIME => $this->faker->numberBetween(60, 5256000), // 1 minute to 10 years in minutes
            UsageStatistic::KEY_TOTAL_REQUESTS => $this->faker->numberBetween(1000, 10000000),
            UsageStatistic::KEY_ACTIVE_SESSIONS => $this->faker->numberBetween(0, 1000),
            'api_calls' => $this->faker->numberBetween(100, 100000),
            'storage_used' => $this->faker->randomFloat(2, 0.01, 10000), // GB
            'memory_usage' => $this->faker->randomFloat(2, 0.1, 100), // Percentage
            'cpu_usage' => $this->faker->randomFloat(2, 0.1, 100), // Percentage
            'disk_io' => $this->faker->numberBetween(10, 10000), // MB/s
            'network_traffic' => $this->faker->numberBetween(100, 1000000), // MB
            default => $this->faker->randomFloat(2, 0, 10000),
        };

        return [
            'stat_type' => $statType,
            'stat_key' => $statKey,
            'stat_value' => $statValue,
        ];
    }

    /**
     * Indicate that the statistic is a global statistic.
     */
    public function global(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'stat_type' => UsageStatistic::TYPE_GLOBAL,
            ];
        });
    }

    /**
     * Indicate that the statistic is a user statistic.
     */
    public function user(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'stat_type' => UsageStatistic::TYPE_USER,
            ];
        });
    }

    /**
     * Indicate that the statistic is a license statistic.
     */
    public function license(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'stat_type' => UsageStatistic::TYPE_LICENSE,
            ];
        });
    }

    /**
     * Indicate that the statistic is a server statistic.
     */
    public function server(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'stat_type' => UsageStatistic::TYPE_SERVER,
            ];
        });
    }

    /**
     * Indicate that the statistic is for login count.
     */
    public function loginCount(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'stat_key' => UsageStatistic::KEY_LOGIN_COUNT,
                'stat_value' => $this->faker->numberBetween(100, 1000000),
            ];
        });
    }

    /**
     * Indicate that the statistic is for usage time.
     */
    public function usageTime(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'stat_key' => UsageStatistic::KEY_USAGE_TIME,
                'stat_value' => $this->faker->numberBetween(60, 5256000), // 1 minute to 10 years in minutes
            ];
        });
    }

    /**
     * Create a statistic with specific type and key.
     */
    public function withSpecific(int $type, string $key, float $value): Factory
    {
        return $this->state(function (array $attributes) use ($type, $key, $value) {
            return [
                'stat_type' => $type,
                'stat_key' => $key,
                'stat_value' => $value,
            ];
        });
    }
}
