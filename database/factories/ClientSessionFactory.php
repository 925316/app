<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\AccountDevice;
use App\Models\ClientSession;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClientSession>
 */
class ClientSessionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ClientSession::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $clientVersions = ['1.0.0', '1.1.0', '1.2.3', '2.0.0', '2.1.0', '2.2.5'];
        $heartbeatOptions = [
            null,
            now()->subMinutes(rand(1, 30)),
            now()->subHours(rand(1, 5)),
            now()->subDays(rand(1, 7)),
        ];

        return [
            'session_token' => Str::random(64),
            'account_id' => Account::factory(),
            'device_id' => AccountDevice::factory(),
            'ip_address' => $this->generateValidIpv4(),
            'client_version' => $this->faker->randomElement($clientVersions),
            'last_heartbeat_at' => $this->faker->randomElement($heartbeatOptions),
            'created_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'updated_at' => fn (array $attributes) => $this->faker->dateTimeBetween($attributes['created_at'], 'now'),
        ];
    }

    /**
     * Generate a valid IPv4 address string.
     */
    private function generateValidIpv4(): string
    {
        return sprintf('%d.%d.%d.%d',
            rand(1, 255),
            rand(0, 255),
            rand(0, 255),
            rand(0, 255)
        );
    }

    /**
     * Indicate that the session is active (recent heartbeat).
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function active(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'last_heartbeat_at' => now()->subMinutes(rand(1, 4)),
            ];
        });
    }

    /**
     * Indicate that the session is expired.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function expired(int $minutesAgo = 30): static
    {
        return $this->state(function (array $attributes) use ($minutesAgo) {
            return [
                'last_heartbeat_at' => now()->subMinutes($minutesAgo),
            ];
        });
    }

    /**
     * Indicate that the session has no heartbeat.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function noHeartbeat(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'last_heartbeat_at' => null,
            ];
        });
    }

    /**
     * Indicate a specific client version.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function version(string $version): static
    {
        return $this->state(function (array $attributes) use ($version) {
            return [
                'client_version' => $version,
            ];
        });
    }

    /**
     * Indicate a specific IP address.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function ip(string $ip): static
    {
        return $this->state(function (array $attributes) use ($ip) {
            return [
                'ip_address' => $ip,
            ];
        });
    }

    /**
     * Indicate a specific account.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function forAccount(Account $account): static
    {
        return $this->state(function (array $attributes) use ($account) {
            return [
                'account_id' => $account->id,
            ];
        });
    }

    /**
     * Indicate a specific device.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function forDevice(AccountDevice $device): static
    {
        return $this->state(function (array $attributes) use ($device) {
            return [
                'device_id' => $device->id,
            ];
        });
    }
}
