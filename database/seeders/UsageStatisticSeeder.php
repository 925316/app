<?php

namespace Database\Seeders;

use App\Models\UsageStatistic;
use Illuminate\Database\Seeder;

class UsageStatisticSeeder extends Seeder
{
    public function run(): void
    {
        UsageStatistic::truncate();

        $globalStats = [
            [
                'stat_key' => 'global.login_count',
                'stat_type' => 0,
                'stat_value' => 0,
                'description' => 'global login count',
            ],
            [
                'stat_key' => 'global.total_usage_time_hours',
                'stat_type' => 0,
                'stat_value' => 0, 
                'description' => 'global usage time',
            ],
            [
                'stat_key' => 'global.active_users',
                'stat_type' => 0,
                'stat_value' => 0,
                'description' => 'global active users',
            ],
            [
                'stat_key' => 'global.total_licenses',
                'stat_type' => 0,
                'stat_value' => 0,
                'description' => 'global total licenses',
            ],
        ];
        
        $userStats = [
            [
                'stat_key' => 'user.login_count',
                'stat_type' => 1,
                'stat_value' => 0,
                'description' => 'user login count',
            ],
            [
                'stat_key' => 'user.usage_time_hours',
                'stat_type' => 1,
                'stat_value' => 0,
                'description' => 'user usage time',
            ]
        ];
   
        $serverStats = [
            [
                'stat_key' => 'server.uptime_percentage',
                'stat_type' => 3,
                'stat_value' => 99.95,
                'description' => 'server uptime percentage',
            ],
            [
                'stat_key' => 'server.api_requests_total',
                'stat_type' => 3,
                'stat_value' => 0,
                'description' => 'server api requests total',
            ],
            [
                'stat_key' => 'server.api_success_rate',
                'stat_type' => 3,
                'stat_value' => 0,
                'description' => 'server api success rate',
            ],
            [
                'stat_key' => 'server.active_sessions',
                'stat_type' => 3,
                'stat_value' => 0,
                'description' => 'server active sessions',
            ],
        ];

        $allStats = array_merge($globalStats, $userStats, $serverStats);

        foreach ($allStats as $stat) {
            UsageStatistic::create([
                'stat_type' => $stat['stat_type'],
                'stat_key' => $stat['stat_key'],
                'stat_value' => $stat['stat_value'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        echo "about " . count($allStats) . " usage statistics created.\n";

    }
}
