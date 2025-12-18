<?php

namespace Database\Seeders;

use App\Models\IpRateLimit;
use Illuminate\Database\Seeder;

class IpRateLimitSeeder extends Seeder
{
    public function run(): void
    {
        IpRateLimit::factory()->count(10)->blocked()->create();

        IpRateLimit::factory()->count(5)->recentlyBlocked()->create();

        IpRateLimit::factory()->count(3)->expiredBlock()->create();

        IpRateLimit::factory()->count(8)->nearThreshold()->create();

        IpRateLimit::factory()->count(15)->oldRecord()->create();

        IpRateLimit::factory()->count(30)->create();

        for ($i = 0; $i < 5; $i++) {
            IpRateLimit::factory()->create([
                'ip_address' => fake()->ipv4(),
                'endpoint' => '/api/auth/login',
                'request_count' => rand(80, 120),
                'last_request_at' => now()->subMinutes(rand(1, 10)),
                'is_blocked' => false,
            ]);
        }

        echo "about " . IpRateLimit::count() . " records created\n";
    }
}
