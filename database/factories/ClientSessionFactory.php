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
        $createdAt = fake()->dateTimeBetween('-30 days', 'now');
        $heartbeatOptions = [
            null,
            fake()->dateTimeBetween($createdAt, 'now'),
            fake()->dateTimeBetween($createdAt, 'now'),
            fake()->dateTimeBetween($createdAt, 'now'),
        ];
        $lastHeartbeat = fake()->randomElement($heartbeatOptions);
        $updatedAt = $lastHeartbeat ?? fake()->dateTimeBetween($createdAt, 'now');

        return [
            'session_token' => Str::random(64),
            'account_id' => Account::factory(),
            'device_id' => AccountDevice::factory(),
            'ip_address' => $this->generateValidIpv4(),
            'client_version' => fake()->randomElement($clientVersions),
            'last_heartbeat_at' => $lastHeartbeat,
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];
    }

    /**
     * Generate a valid IPv4 address string.
     */
    private function generateValidIpv4(): string
    {
        return sprintf('%d.%d.%d.%d',
            fake()->numberBetween(1, 255),
            fake()->numberBetween(0, 255),
            fake()->numberBetween(0, 255),
            fake()->numberBetween(0, 255)
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
            $createdAt = $attributes['created_at'] ?? fake()->dateTimeBetween('-30 days', 'now');
            $lastHeartbeat = fake()->dateTimeBetween($createdAt, 'now');

            return [
                'created_at' => $createdAt,
                'last_heartbeat_at' => $lastHeartbeat,
                'updated_at' => $lastHeartbeat,
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
            $createdAt = $attributes['created_at'] ?? fake()->dateTimeBetween('-30 days', 'now');
            $lastHeartbeat = now()->subMinutes($minutesAgo);

            if ($lastHeartbeat->lessThan($createdAt)) {
                $lastHeartbeat = fake()->dateTimeBetween($createdAt, 'now');
            }

            return [
                'created_at' => $createdAt,
                'last_heartbeat_at' => $lastHeartbeat,
                'updated_at' => $lastHeartbeat,
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
