<?php

namespace Database\Seeders;

use App\Models\License;
use Illuminate\Database\Seeder;

class LicenseSeeder extends Seeder
{
    public function run(): void
    {
        License::factory()->count(350)->create();
        License::factory()->count(50)->active()->create();
        License::factory()->count(50)->expired()->create();
        License::factory()->count(30)->suspended()->create();
        License::factory()->count(20)->upgraded()->create();
        License::factory()->count(20)->revoked()->create();

        License::factory()->create([
            'key' => 'ADMIN-00000-00000-00000-00000',
            'privilege' => 5,
            'type' => 1,
            'notes' => 'Owner',
        ]);
        
        License::factory()->create([
            'key' => 'TEST0-00000-00000-00000-00000',
            'privilege' => 4,
            'type' => 1,
            'notes' => 'staff_bela',
        ]);

        echo "about " . License::count() . " licenses created.\n";
    }
}