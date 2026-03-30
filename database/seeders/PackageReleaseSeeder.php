<?php

namespace Database\Seeders;

use App\Models\PackageRelease;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PackageReleaseSeeder extends Seeder
{
    private const BETA_INSERTION_INTERVAL = 5;

    private const MINIMUM_COMMIT_DATES_FOR_GIT_TIMELINE = 15;

    private const OLDER_BUCKET_YEAR = 25;

    private const OLDER_BUCKET_MONTH = 3;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $releases = $this->buildReleaseTimelineFromGit();

        foreach ($releases as $release) {
            PackageRelease::query()->updateOrCreate(
                ['version' => $release['version']],
                $release
            );
        }

        $this->displayPackageStats();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildReleaseTimelineFromGit(): array
    {
        $commitDates = $this->readCommitDates();
        if (count($commitDates) < self::MINIMUM_COMMIT_DATES_FOR_GIT_TIMELINE) {
            return $this->buildFallbackReleaseTimeline();
        }

        $oneYearAgo = now()->subYear()->startOfDay();
        $olderCommits = [];
        $recentMonthlyBuckets = [];

        foreach ($commitDates as $date) {
            if ($date->lessThan($oneYearAgo)) {
                $olderCommits[] = $date;

                continue;
            }

            $key = sprintf('%02d.%d', (int) $date->format('y'), (int) $date->format('n'));
            if (! isset($recentMonthlyBuckets[$key])) {
                $recentMonthlyBuckets[$key] = [];
            }
            $recentMonthlyBuckets[$key][] = $date;
        }

        ksort($recentMonthlyBuckets);

        $releases = [];

        if ($olderCommits !== []) {
            usort($olderCommits, fn (Carbon $a, Carbon $b): int => $a->lessThan($b) ? -1 : 1);
            $releases = [
                ...$releases,
                ...$this->buildMonthlyReleases(
                    year: self::OLDER_BUCKET_YEAR,
                    month: self::OLDER_BUCKET_MONTH,
                    count: count($olderCommits),
                    timestamps: $olderCommits
                ),
            ];
        }

        foreach ($recentMonthlyBuckets as $bucket => $dates) {
            [$year, $month] = array_map('intval', explode('.', $bucket));

            usort($dates, fn (Carbon $a, Carbon $b): int => $a->lessThan($b) ? -1 : 1);
            $releases = [
                ...$releases,
                ...$this->buildMonthlyReleases(
                    year: $year,
                    month: $month,
                    count: count($dates),
                    timestamps: $dates,
                ),
            ];
        }

        if (! $this->containsDevelopmentRelease($releases)) {
            return $this->buildFallbackReleaseTimeline();
        }

        return $releases;
    }

    /**
     * @param  array<int, array<string, mixed>>  $releases
     */
    private function containsDevelopmentRelease(array $releases): bool
    {
        foreach ($releases as $release) {
            if (is_array($release) && ($release['release_channel'] ?? null) === 'dev') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, Carbon>  $timestamps
     * @return array<int, array<string, mixed>>
     */
    private function buildMonthlyReleases(int $year, int $month, int $count, array $timestamps): array
    {
        $releases = [];
        $stableSequence = 0;

        for ($index = 0; $index < $count; $index++) {
            $isBeta = (($index + 1) % self::BETA_INSERTION_INTERVAL) === 0;
            if (! $isBeta) {
                $stableSequence++;
            }

            $baseVersion = sprintf('%02d.%d.%d', $year, $month, $stableSequence);
            $version = $isBeta ? $baseVersion.'-beta' : $baseVersion;
            $channel = $isBeta ? 'dev' : 'stable';

            $publishedAt = $timestamps[$index] ?? now()->subDays(120 - $index);
            $downloadUrl = $this->buildDownloadUrl($version, $channel);
            $virusToken = hash('sha256', $version.'|'.$downloadUrl);

            $releases[] = [
                'version' => $version,
                'release_channel' => $channel,
                'download_url' => $downloadUrl,
                'virus_detection_url' => "https://www.virustotal.com/gui/file/{$virusToken}",
                'changelog' => $this->buildChangelog($version, $channel),
                'created_at' => $publishedAt,
                'updated_at' => $publishedAt,
            ];
        }

        return $releases;
    }

    /**
     * @return array<int, Carbon>
     */
    private function readCommitDates(): array
    {
        $output = null;

        if (function_exists('shell_exec')) {
            $output = shell_exec('git log --date=iso-strict --pretty=%ad');
        }

        if (! is_string($output) || trim($output) === '') {
            return [];
        }

        $dates = [];
        $lines = preg_split('/\r\n|\r|\n/', trim($output));
        if (! is_array($lines)) {
            return [];
        }

        foreach ($lines as $line) {
            if (! is_string($line) || trim($line) === '') {
                continue;
            }

            try {
                $dates[] = Carbon::parse(trim($line));
            } catch (\Throwable) {
                continue;
            }
        }

        return $dates;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildFallbackReleaseTimeline(): array
    {
        $now = now();
        $releases = [];

        $fallbackMonths = [
            [$now->copy()->subMonths(2), 20],
            [$now->copy()->subMonths(1), 16],
            [$now->copy(), 24],
        ];

        foreach ($fallbackMonths as [$monthDate, $count]) {
            if (! $monthDate instanceof Carbon || ! is_int($count)) {
                continue;
            }

            $year = (int) $monthDate->format('y');
            $month = (int) $monthDate->format('n');
            $timestamps = [];

            for ($i = 0; $i < $count; $i++) {
                $timestamps[] = $monthDate->copy()->startOfMonth()->addDays(min($i, 27))->addHours(10 + ($i % 8));
            }

            $releases = [
                ...$releases,
                ...$this->buildMonthlyReleases($year, $month, $count, $timestamps),
            ];
        }

        return $releases;
    }

    private function buildDownloadUrl(string $version, string $channel): string
    {
        $platforms = ['win-x64', 'linux-x64', 'macos-universal'];
        $index = abs(crc32($version.'|'.$channel)) % count($platforms);
        $platform = $platforms[$index];

        return "https://downloads.demo-license.local/releases/{$channel}/{$version}/acme-client-{$version}-{$platform}.zip";
    }

    private function buildChangelog(string $version, string $channel): string
    {
        $title = $channel === 'stable'
            ? 'Stable release with fixes and usability improvements.'
            : 'Beta pre-release for staged validation and regression checks.';

        return "# {$version}\n\n{$title}\n\n### Changes\n- Improved session and license lifecycle consistency\n- Fixed edge-case input handling and state transitions\n- Improved update check reliability\n";
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
}
