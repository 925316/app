<?php

namespace Database\Seeders;

use App\Models\PackageRelease;
use Illuminate\Database\Seeder;

class PackageReleaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $releases = $this->buildReleaseTimeline();

        foreach ($releases as $release) {
            PackageRelease::query()->updateOrCreate(
                ['version' => $release['version']],
                $release
            );
        }

        $this->displayPackageStats();
    }

    /**
     * Build a realistic release timeline within the last 365 days.
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildReleaseTimeline(): array
    {
        $anchor = now()->startOfDay();

        $schedule = [
            ['version' => '2.0.0', 'channel' => 'stable', 'days_ago' => 330, 'summary' => 'Major release baseline with a refreshed activation workflow and better telemetry defaults.'],
            ['version' => '2.0.1', 'channel' => 'stable', 'days_ago' => 304, 'summary' => 'Patch release focused on session resilience and edge-case license validation.'],
            ['version' => '2.0.2', 'channel' => 'stable', 'days_ago' => 276, 'summary' => 'Maintenance patch reducing race conditions during concurrent device binding.'],
            ['version' => '2.1.0', 'channel' => 'stable', 'days_ago' => 235, 'summary' => 'Minor release adding richer account diagnostics and cleanup tooling.'],
            ['version' => '2.1.1', 'channel' => 'stable', 'days_ago' => 207, 'summary' => 'Patch release improving heartbeat processing stability under load.'],
            ['version' => '2.2.0', 'channel' => 'stable', 'days_ago' => 160, 'summary' => 'Minor release with package distribution hardening and improved audit events.'],
            ['version' => '2.2.1', 'channel' => 'stable', 'days_ago' => 138, 'summary' => 'Security-focused patch with stricter license status transitions.'],
            ['version' => '2.3.0-beta.1', 'channel' => 'dev', 'days_ago' => 54, 'summary' => 'Beta build for the 2.3 line, suitable for integration testing only.'],
            ['version' => '2.3.0-beta.2', 'channel' => 'dev', 'days_ago' => 38, 'summary' => 'Second beta with stability fixes from partner feedback.'],
            ['version' => '2.3.0-rc.1', 'channel' => 'dev', 'days_ago' => 23, 'summary' => 'Release candidate with all target features frozen for final validation.'],
            ['version' => '2.3.0', 'channel' => 'stable', 'days_ago' => 17, 'summary' => 'Stable 2.3 release after release-candidate verification completed.'],
            ['version' => '2.3.1', 'channel' => 'stable', 'days_ago' => 5, 'summary' => 'Post-release patch for regression fixes and download reliability updates.'],
        ];

        $releases = [];

        foreach ($schedule as $item) {
            $publishedAt = $anchor->copy()->subDays($item['days_ago'])->setTime(
                random_int(9, 18),
                random_int(0, 59),
                random_int(0, 59)
            );

            $downloadUrl = $this->buildDownloadUrl($item['version'], $item['channel']);
            $virusToken = hash('sha256', $item['version'].'|'.$downloadUrl);

            $releases[] = [
                'version' => $item['version'],
                'release_channel' => $item['channel'],
                'download_url' => $downloadUrl,
                'virus_detection_url' => $item['channel'] === 'stable' || random_int(0, 100) < 45
                    ? "https://www.virustotal.com/gui/file/{$virusToken}"
                    : null,
                'changelog' => $this->generateSimpleChangelog($item['version'], $item['summary']),
                'created_at' => $publishedAt,
                'updated_at' => $publishedAt,
            ];
        }

        return $releases;
    }

    private function buildDownloadUrl(string $version, string $channel): string
    {
        $platform = fake()->randomElement(['win-x64', 'linux-x64', 'macos-universal']);

        return "https://downloads.demo-license.local/releases/{$channel}/{$version}/acme-client-{$version}-{$platform}.zip";
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

        $this->command->info("Total package releases: {$total}");
        $this->command->info("Stable releases: {$stable}");
        $this->command->info("Development releases: {$dev}");
        $this->command->info("Releases with virus detection: {$withVirusDetection}");

        $latestReleases = PackageRelease::query()
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        if ($latestReleases->isNotEmpty()) {
            $this->command->info('');
            $this->command->info('Latest releases:');
            foreach ($latestReleases as $release) {
                $date = $release->created_at?->format('Y-m-d') ?? 'n/a';
                $this->command->info("  {$release->version} ({$release->release_channel}) - {$date}");
            }
        }

        $this->command->info(str_repeat('-', 50));
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

        $featuresSelected = array_map(fn ($i) => $features[$i], (array) array_rand($features, fake()->numberBetween(1, 2)));
        $fixesSelected = array_map(fn ($i) => $fixes[$i], (array) array_rand($fixes, fake()->numberBetween(1, 2)));
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
