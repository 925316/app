<?php

namespace Database\Seeders;

use App\Models\PackageRelease;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PackageReleaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Clear existing records
        PackageRelease::truncate();

        // Create initial stable releases
        PackageRelease::factory()->count(5)->stable()->create([
            'version' => fn() => $this->generateStableVersion(),
        ]);

        // Create some development releases
        PackageRelease::factory()->count(3)->dev()->create([
            'version' => fn() => $this->generateDevVersion(),
        ]);

        // Create specific milestone releases
        $this->createMilestoneReleases();
    }

    /**
     * Generate a stable version number.
     *
     * @return string
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
     *
     * @return string
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
     *
     * @return void
     */
    private function createMilestoneReleases(): void
    {
        $milestones = [
            [
                'version' => '1.0.0',
                'release_channel' => 'stable',
                'download_url' => 'https://example.com/downloads/v1.0.0/package.zip',
                'checksum_sha256' => hash('sha256', 'initial-release-v1.0.0'),
                'changelog' => $this->generateMilestoneChangelog('1.0.0', true),
            ],
            [
                'version' => '2.0.0',
                'release_channel' => 'stable',
                'download_url' => 'https://example.com/downloads/v2.0.0/package.zip',
                'checksum_sha256' => hash('sha256', 'major-release-v2.0.0'),
                'changelog' => $this->generateMilestoneChangelog('2.0.0'),
            ],
            [
                'version' => '2.1.0-rc',
                'release_channel' => 'dev',
                'download_url' => 'https://example.com/downloads/v2.1.0-rc/package.zip',
                'checksum_sha256' => hash('sha256', 'release-candidate-v2.1.0-rc'),
                'changelog' => $this->generateMilestoneChangelog('2.1.0-rc', false, true),
            ],
        ];

        foreach ($milestones as $milestone) {
            PackageRelease::factory()->create($milestone);
        }
    }

    /**
     * Generate a detailed milestone changelog.
     *
     * @param string $version
     * @param bool $isInitial
     * @param bool $isReleaseCandidate
     * @return string
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