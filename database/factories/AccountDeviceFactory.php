<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Account;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class AccountDeviceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstSeen = $this->faker->dateTimeBetween('-1 year', '-1 month');
        $lastSeen = Carbon::parse($firstSeen)->addDays(rand(0, 30));
        
        $isBound = $this->faker->boolean(80);
        $boundAt = $isBound 
            ? Carbon::parse($firstSeen)->addDays(rand(0, 7))
            : null;
        
        $isUnbound = $isBound && $this->faker->boolean(30);
        $unboundAt = $isUnbound 
            ? Carbon::parse($boundAt)->addDays(rand(1, 60))
            : null;
  
        return [
            'account_id'    => \App\Models\Account::factory(),
            'hwid_hash'     => hash('sha256', $this->faker->uuid() . microtime()),
            'ip_address'    => $this->faker->ipv4(),
            'country_code'  => $this->faker->countryCode(),
            'first_seen_at' => $firstSeen,
            'last_seen_at'  => $lastSeen,
            'bound_at'      => $boundAt,
            'unbound_at'    => $unboundAt,
            'created_at'    => $firstSeen,
            'updated_at'    => $lastSeen,
        ];
    }

    /**
     * Indicate that the device is currently bound.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function bound(): static
    {
        return $this->state(fn (array $attributes) => [
            'bound_at'   => Carbon::parse($attributes['first_seen_at'])->addDays(rand(0, 7)),
            'unbound_at' => null,
        ]);
    }

    /**
     * Indicate that the device is unbound.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unbound(): static
    {
        return $this->state(fn (array $attributes) => [
            'bound_at'   => Carbon::parse($attributes['first_seen_at'])->addDays(rand(0, 7)),
            'unbound_at' => Carbon::now()->subDays(rand(1, 30)),
        ]);
    }

    /**
     * Indicate that the device was never bound.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function neverBound(): static
    {
        return $this->state(fn (array $attributes) => [
            'bound_at'   => null,
            'unbound_at' => null,
        ]);
    }

    /**
     * Indicate that the device is active (seen recently).
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_seen_at' => Carbon::now()->subHours(rand(1, 24)),
        ]);
    }

    /**
     * Indicate that the device is inactive.
     *
     * @param int $days
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function inactive(int $days = 60): static
    {
        return $this->state(fn (array $attributes) => [
            'last_seen_at' => Carbon::now()->subDays(rand($days, $days + 30)),
        ]);
    }

    /**
     * Indicate a specific country for the device.
     *
     * @param string $countryCode
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function country(string $countryCode): static
    {
        return $this->state(fn (array $attributes) => [
            'country_code' => $countryCode,
        ]);
    }

    /**
     * Indicate a specific IP range.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function localNetwork(): static
    {
        return $this->state(fn (array $attributes) => [
            'ip_address' => $this->faker->ipv4('192.168.0.0/16'),
        ]);
    }
}
