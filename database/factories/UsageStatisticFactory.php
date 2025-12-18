<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UsageStatisticFactory extends Factory
{
    public function definition(): array
    {
        // TODO: delete this
        return [
            'stat_type' => 0, // global
            'stat_key' => 'placeholder',
            'stat_value' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}