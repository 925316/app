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

        // Create some additional development releases
        $devReleases = [
            ['3.0.0-alpha.1', 'dev', 'https://example.com/downloads/v3.0.0-alpha.1.zip', 'Early alpha testing for v3.0.0'],
            ['3.0.0-beta.1', 'dev', 'https://example.com/downloads/v3.0.0-beta.1.zip', 'Beta testing with improved stability'],
            ['3.0.0-rc.1', 'dev', 'https://example.com/downloads/v3.0.0-rc.1.zip', 'Release candidate for v3.0.0'],
        ];

        foreach ($devReleases as $release) {
            PackageRelease::factory()->create([
                'version' => $release[0],
                'release_channel' => $release[1],
                'download_url' => $release[2],
                'checksum_sha256' => null, // No checksum for remote files
                'changelog' => $release[3],
            ]);
        }

        $this->command->info(' Development package releases created successfully!');
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
        $withChecksum = PackageRelease::whereNotNull('checksum_sha256')->count();
        $withChangelog = PackageRelease::whereNotNull('changelog')->count();

        $this->command->info("Total package releases: {$total}");
        $this->command->info("Stable releases: {$stable}");
        $this->command->info("Development releases: {$dev}");
        $this->command->info("Releases with checksum: {$withChecksum}");
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
     * Generate a unique stable version number.
     */
    private function generateUniqueStableVersion(): string
    {
        do {
            $version = $this->generateStableVersion();
        } while (in_array($version, $this->usedVersions));

        $this->usedVersions[] = $version;

        return $version;
    }

    /**
     * Generate a unique development version number.
     */
    private function generateUniqueDevVersion(): string
    {
        do {
            $version = $this->generateDevVersion();
        } while (in_array($version, $this->usedVersions));

        $this->usedVersions[] = $version;

        return $version;
    }

    /**
     * Generate a stable version number.
     */
    private function generateStableVersion(): string
    {
        $major = rand(1, 3);
        $minor = rand(0, 5);
        $patch = rand(0, 20);

        return "{$major}.{$minor}.{$patch}";
    }

    /**
     * Generate a development version number.
     */
    private function generateDevVersion(): string
    {
        $major = rand(4, 5);
        $minor = rand(0, 2);
        $patch = rand(0, 10);

        $preRelease = rand(0, 1) ? '-alpha' : '-beta';

        return "{$major}.{$minor}.{$patch}{$preRelease}";
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
                'download_url' => 'https://example.com/downloads/v1.0.0/package.zip',
                'checksum_sha256' => null, // No checksum for remote files
                'changelog' => $this->generateMilestoneChangelog('1.0.0', true),
            ],
            [
                'version' => '2.0.0',
                'release_channel' => 'stable',
                'download_url' => 'https://example.com/downloads/v2.0.0/package.zip',
                'checksum_sha256' => null, // No checksum for remote files
                'changelog' => $this->generateMilestoneChangelog('2.0.0'),
            ],
            [
                'version' => '2.1.0-rc',
                'release_channel' => 'dev',
                'download_url' => 'https://example.com/downloads/v2.1.0-rc/package.zip',
                'checksum_sha256' => null, // No checksum for remote files
                'changelog' => $this->generateMilestoneChangelog('2.1.0-rc', false, true),
            ],
        ];

        foreach ($milestones as $milestone) {
            // Check if this version already exists to make the seeder idempotent
            if (!PackageRelease::where('version', $milestone['version'])->exists()) {
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
            if (!PackageRelease::where('version', $release['version'])->exists()) {
                PackageRelease::factory()->create($release);
            }
        }

        $this->command->info('Created '.count($releases).' package releases in chronological order.');
    }

    /**
     * Generate a detailed milestone changelog.
     */
    private function generateMilestoneChangelog(
        string $version,
        bool $isInitial = false,
        bool $isReleaseCandidate = false
    ): string {
        $title = $isInitial
            ? "🎉 Initial Release {$version}"
            : ($isReleaseCandidate
                ? "🚀 Release Candidate {$version}"
                : "✨ Major Release {$version}");

        return <<<CHANGELOG
        # {$title}

        ## Overview
        This release represents a significant milestone in our development journey.

        ## Breaking Changes
        - Updated API endpoints for better consistency
        - Improved database schema for better performance

        ## New Features
        - Added comprehensive documentation
        - Implemented new authentication system
        - Enhanced error handling and logging

        ## Improvements
        - Optimized database queries
        - Improved response times by 40%
        - Enhanced security measures

        ## Bug Fixes
        - Fixed memory leak in background processing
        - Resolved race condition in concurrent requests
        - Patched security vulnerability in file uploads

        ## Migration Notes
        Please backup your data before upgrading.

        CHANGELOG;
    }
}
