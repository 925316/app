<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Account;

class ClientSessionFactory extends Factory
{
    public function definition(): array
    {
        $createdAt = now()->subDays($this->faker->numberBetween(0, 90));
        $lastHeartbeatAt = (clone $createdAt)->addHours($this->faker->numberBetween(0, 48));

        return [
            'session_token' => hash('sha512', $this->faker->unique()->uuid()),
            'account_id' => Account::factory(),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'client_version' => $this->generateVersion(),
            'language' => $this->faker->countryCode(),
            'session_data' => json_encode([
                'last_activity' => $lastHeartbeatAt->timestamp,
            ]),
            'last_heartbeat_at' => $lastHeartbeatAt,
            'created_at' => $createdAt,
            'updated_at' => $lastHeartbeatAt,
        ];
    }

    public function active(): static
    {
        return $this->state([
            'last_heartbeat_at' => now()->subMinutes($this->faker->numberBetween(1, 10)),
            'session_data' => json_encode([
                'last_activity' => now()->timestamp,
                'client_settings' => [
                    'theme' => $this->faker->randomElement(['light', 'dark']),
                    'notifications' => true,
                    'auto_update' => $this->faker->boolean(),
                ],
            ]),
        ]);
    }

    public function expired(): static
    {
        $lastHeartbeatAt = now()->subHours($this->faker->numberBetween(3, 72));
        
        return $this->state([
            'last_heartbeat_at' => $lastHeartbeatAt,
            'session_data' => json_encode([
                'last_activity' => $lastHeartbeatAt->timestamp,
                'client_settings' => [
                    'theme' => $this->faker->randomElement(['light', 'dark']),
                    'notifications' => $this->faker->boolean(),
                    'auto_update' => false,
                ],
            ]),
        ]);
    }

    public function adminSession(): static
    {
        return $this->state([
            'user_agent' => 'Admin Console v1.0',
            'ip_address' => '127.0.0.1',
            'client_version' => '1.0.0',
            'language' => 'en',
            'session_data' => json_encode([
                'last_activity' => now()->timestamp,
                'client_settings' => [
                    'theme' => 'dark',
                    'notifications' => true,
                    'admin_mode' => true,
                ],
            ]),
        ]);
    }

    private function generateVersion(): string
    {
        $major = $this->faker->randomElement([1, 2, 3]);
        $minor = $this->faker->numberBetween(0, 10);
        $patch = $this->faker->numberBetween(0, 50);
        
        return "{$major}.{$minor}.{$patch}";
    }
}