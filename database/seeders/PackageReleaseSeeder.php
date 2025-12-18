<?php

namespace Database\Seeders;

use App\Models\PackageRelease;
use Illuminate\Database\Seeder;

class PackageReleaseSeeder extends Seeder
{
    public function run(): void
    {
        PackageRelease::factory()->create([
            'version' => '1.0.0',
            'release_channel' => 'stable',
            'download_url' => 'https://example.com/downloads/package-v1.0.0.zip',
            'changelog' => implode("\n", [
                'Initial stable release',
                'Added license management',
                'Fixed authentication issues',
            ]),
            'download_count' => 1000,
        ]);

        PackageRelease::factory()->create([
            'version' => '1.1.0',
            'release_channel' => 'stable',
            'download_url' => 'https://example.com/downloads/package-v1.1.0.zip',
            'changelog' => implode("\n", [
                'Performance improvements',
                'Added dark mode',
                'Bug fixes',
            ]),
            'download_count' => 500,
        ]);

        PackageRelease::factory()->create([
            'version' => '2.0.0-beta',
            'release_channel' => 'dev',
            'download_url' => 'https://example.com/downloads/package-v2.0.0-beta.zip',
            'changelog' => 'Experimental features for testing',
            'download_count' => 50,
        ]);

        PackageRelease::factory()->count(8)->create();
        PackageRelease::factory()->count(3)->popular()->create();
        PackageRelease::factory()->count(2)->dev()->recent()->create();

        echo "about " . PackageRelease::count() . " package releases created.\n";
    }
}
