<?php

namespace Database\Seeders;

use App\Models\PackageRelease;
use Illuminate\Database\Seeder;

class PackageReleaseSeeder extends Seeder
{
    private array $usedVersions = [];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset used versions tracking
        $this->usedVersions = [];

        // Create releases
        $this->createStableReleases();
        $this->createDevelopmentReleases();
        $this->displayPackageStats();
    }

    /**
     * Create stable package releases.
     */
    private function createStableReleases(): void
    {
        // Create milestone releases first
        $this->createMilestoneReleases();

        // Create chronological releases
        $this->createChronologicalReleases();
    }

    /**
     * Create development package releases.
     */
    private function createDevelopmentReleases(): void
    {
        $this->command->info('Creating development package releases...');

        // Create some additional development releases with random URLs
        $devReleases = [
            [
                'version' => '3.0.0-alpha.1',
                'release_channel' => 'dev',
                'changelog' => $this->generateSimpleChangelog('3.0.0-alpha.1', 'Early alpha - complete rewrite of the core engine. Not recommended for production use.'),
            ],
            [
                'version' => '3.0.0-beta.1',
                'release_channel' => 'dev',
                'changelog' => $this->generateSimpleChangelog('3.0.0-beta.1', 'Public beta. Most features are stable; known issues with session handling under load.'),
            ],
            [
                'version' => '3.0.0-rc.1',
                'release_channel' => 'dev',
                'changelog' => $this->generateSimpleChangelog('3.0.0-rc.1', 'Release candidate. All planned features implemented; final round of testing before stable release.'),
            ],
        ];

        foreach ($devReleases as $release) {
            PackageRelease::factory()->create([
                'version' => $release['version'],
                'release_channel' => $release['release_channel'],
                'download_url' => $this->generateRandomDownloadUrl($release['version']),
                'virus_detection_url' => $this->generateRandomVirusUrl(),
                'changelog' => $release['changelog'],
            ]);
        }

        $this->command->info(' Development package releases created successfully!');
    }

    /**
     * Generate a random download URL.
     */
    private function generateRandomDownloadUrl(string $version): string
    {
        $paths = [
            'downloads/releases',
            'files/packages',
            'static/builds',
            'cdn/distributions',
        ];

        $path = $paths[array_rand($paths)];
        $randomHash = strtolower(substr(md5($version.time()), 0, 8));

        return "https://cdn.example.com/{$path}/{$version}-{$randomHash}.zip";
    }

    /**
     * Generate a random virus detection URL.
     */
    private function generateRandomVirusUrl(): string
    {
        $providers = [
            'virustotal.com/file',
            'scan.secure.com/check',
            'verify.safe-api.com/scan',
        ];

        $provider = $providers[array_rand($providers)];
        $randomId = strtolower(substr(uniqid(), 0, 16));

        return "https://{$provider}/{$randomId}";
    }

    /**
     * Display package statistics.
     */
    private function displayPackageStats(): void
    {
        $this->command->info(str_repeat('-', 50));
        $this->command->info('PACKAGE RELEASE STATISTICS');
        $this->command->info(str_repeat('-', 50));

        $total = PackageRelease::count();
        $stable = PackageRelease::where('release_channel', 'stable')->count();
        $dev = PackageRelease::where('release_channel', 'dev')->count();
        $withVirusDetection = PackageRelease::whereNotNull('virus_detection_url')->count();
        $withChangelog = PackageRelease::whereNotNull('changelog')->count();

        $this->command->info("Total package releases: {$total}");
        $this->command->info("Stable releases: {$stable}");
        $this->command->info("Development releases: {$dev}");
        $this->command->info("Releases with virus detection: {$withVirusDetection}");
        $this->command->info("Releases with changelog: {$withChangelog}");

        // Show latest releases
        $latestReleases = PackageRelease::orderBy('created_at', 'desc')->limit(5)->get();

        if ($latestReleases->isNotEmpty()) {
            $this->command->info('');
            $this->command->info('Latest releases:');
            foreach ($latestReleases as $release) {
                $this->command->info("  {$release->version} ({$release->release_channel}) - {$release->created_at->format('Y-m-d')}");
            }
        }

        $this->command->info(str_repeat('-', 50));
    }

    /**
     * Create specific milestone releases.
     */
    private function createMilestoneReleases(): void
    {
        $milestones = [
            [
                'version' => '1.0.0',
                'release_channel' => 'stable',
                'download_url' => $this->generateRandomDownloadUrl('1.0.0'),
                'virus_detection_url' => $this->generateRandomVirusUrl(),
                'changelog' => $this->generateSimpleChangelog('1.0.0', 'Initial release'),
            ],
            [
                'version' => '2.0.0',
                'release_channel' => 'stable',
                'download_url' => $this->generateRandomDownloadUrl('2.0.0'),
                'virus_detection_url' => $this->generateRandomVirusUrl(),
                'changelog' => $this->generateSimpleChangelog('2.0.0', 'Major update'),
            ],
            [
                'version' => '2.1.0-rc',
                'release_channel' => 'dev',
                'download_url' => $this->generateRandomDownloadUrl('2.1.0-rc'),
                'virus_detection_url' => $this->generateRandomVirusUrl(),
                'changelog' => $this->generateSimpleChangelog('2.1.0-rc', 'Release candidate'),
            ],
        ];

        foreach ($milestones as $milestone) {
            // Check if this version already exists to make the seeder idempotent
            if (! PackageRelease::where('version', $milestone['version'])->exists()) {
                // Track milestone versions to avoid conflicts
                $this->usedVersions[] = $milestone['version'];
                PackageRelease::factory()->create($milestone);
            }
        }
    }

    /**
     * Create releases in chronological order with realistic progression.
     */
    private function createChronologicalReleases(): void
    {
        $releases = [];

        // Start with version 1.0.1 (1.0.0 is already created in milestone releases)
        $currentDate = now()->subYears(2);
        $releases[] = [
            'version' => '1.0.1',
            'release_channel' => 'stable',
            'created_at' => $currentDate,
            'updated_at' => $currentDate,
        ];

        // Add patch releases for 1.x (starting from 1.0.2 since 1.0.1 is already added)
        for ($patch = 2; $patch <= 5; $patch++) {
            $currentDate = $currentDate->addDays(rand(14, 60)); // 2-8 weeks between releases
            $releases[] = [
                'version' => "1.0.{$patch}",
                'release_channel' => 'stable',
                'created_at' => $currentDate,
                'updated_at' => $currentDate,
            ];
        }

        // Minor version bump to 1.1.0
        $currentDate = $currentDate->addDays(rand(21, 90)); // 3 weeks to 3 months
        $releases[] = [
            'version' => '1.1.0',
            'release_channel' => 'stable',
            'created_at' => $currentDate,
            'updated_at' => $currentDate,
        ];

        // Continue with more 1.x releases
        for ($minor = 2; $minor <= 5; $minor++) {
            $currentDate = $currentDate->addDays(rand(30, 120)); // 1-4 months
            $releases[] = [
                'version' => "1.{$minor}.0",
                'release_channel' => 'stable',
                'created_at' => $currentDate,
                'updated_at' => $currentDate,
            ];

            // Add some patch releases
            $patches = rand(0, 3);
            for ($patch = 1; $patch <= $patches; $patch++) {
                $currentDate = $currentDate->addDays(rand(7, 30)); // 1 week to 1 month
                $releases[] = [
                    'version' => "1.{$minor}.{$patch}",
                    'release_channel' => 'stable',
                    'created_at' => $currentDate,
                    'updated_at' => $currentDate,
                ];
            }
        }

        // Major version bump to 2.0.0
        $currentDate = $currentDate->addDays(rand(60, 180)); // 2-6 months
        $releases[] = [
            'version' => '2.0.0',
            'release_channel' => 'stable',
            'created_at' => $currentDate,
            'updated_at' => $currentDate,
        ];

        // Continue with 2.x releases
        for ($minor = 1; $minor <= 3; $minor++) {
            $currentDate = $currentDate->addDays(rand(45, 120)); // 1.5-4 months
            $releases[] = [
                'version' => "2.{$minor}.0",
                'release_channel' => 'stable',
                'created_at' => $currentDate,
                'updated_at' => $currentDate,
            ];

            // Add patch releases
            $patches = rand(1, 4);
            for ($patch = 1; $patch <= $patches; $patch++) {
                $currentDate = $currentDate->addDays(rand(10, 45)); // 10 days to 1.5 months
                $releases[] = [
                    'version' => "2.{$minor}.{$patch}",
                    'release_channel' => 'stable',
                    'created_at' => $currentDate,
                    'updated_at' => $currentDate,
                ];
            }
        }

        // Add some development/beta releases
        $devReleases = [
            ['version' => '2.4.0-alpha.1', 'days' => rand(15, 45)],
            ['version' => '2.4.0-beta.1', 'days' => rand(10, 30)],
            ['version' => '2.4.0-beta.2', 'days' => rand(7, 21)],
            ['version' => '2.4.0-rc.1', 'days' => rand(3, 14)],
        ];

        foreach ($devReleases as $devRelease) {
            $currentDate = $currentDate->addDays($devRelease['days']);
            $releases[] = [
                'version' => $devRelease['version'],
                'release_channel' => 'dev',
                'created_at' => $currentDate,
                'updated_at' => $currentDate,
            ];
        }

        // Final stable release
        $currentDate = $currentDate->addDays(rand(1, 7));
        $releases[] = [
            'version' => '2.4.0',
            'release_channel' => 'stable',
            'created_at' => $currentDate,
            'updated_at' => $currentDate,
        ];

        // Create the releases
        foreach ($releases as $release) {
            // Check if this version already exists to make the seeder idempotent
            if (! PackageRelease::where('version', $release['version'])->exists()) {
                PackageRelease::factory()->create($release);
            }
        }

        $this->command->info('Created '.count($releases).' package releases in chronological order.');
    }

    /**
     * Generate a realistic changelog for a release.
     */
    private function generateSimpleChangelog(string $version, string $description): string
    {
        $features = [
            'Added support for hardware ID binding with automatic device detection',
            'Introduced license upgrade path from Standard to Ultimate tier',
            'New two-factor authentication support for enhanced account security',
            'Added session heartbeat monitoring for active client connections',
            'Implemented automatic license expiry notifications',
            'Added admin dashboard with real-time usage statistics',
        ];

        $fixes = [
            'Fixed license activation failing when device count exceeded limit',
            'Resolved incorrect privilege level after license upgrade',
            'Fixed session token not invalidating on logout',
            'Corrected HWID hash collision causing false device duplicates',
            'Fixed timezone handling for license expiry dates',
            'Resolved race condition in concurrent device binding requests',
        ];

        $improvements = [
            'Improved license key validation performance by 40%',
            'Reduced database queries during session validation',
            'Optimized device binding flow to reduce latency',
            'Enhanced error messages for invalid license states',
            'Improved stability under high concurrent session load',
        ];

        $featuresSelected = array_map(fn ($i) => $features[$i], (array) array_rand($features, rand(1, 2)));
        $fixesSelected = array_map(fn ($i) => $fixes[$i], (array) array_rand($fixes, rand(1, 2)));
        $improvementsSelected = array_map(fn ($i) => $improvements[$i], (array) array_rand($improvements, 1));

        $changelog = "# {$version}\n\n{$description}\n\n";
        $changelog .= "### New Features\n";
        foreach ($featuresSelected as $item) {
            $changelog .= "- {$item}\n";
        }
        $changelog .= "\n### Bug Fixes\n";
        foreach ($fixesSelected as $item) {
            $changelog .= "- {$item}\n";
        }
        $changelog .= "\n### Improvements\n";
        foreach ($improvementsSelected as $item) {
            $changelog .= "- {$item}\n";
        }

        return $changelog;
    }
}
