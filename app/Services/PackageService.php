<?php

namespace App\Services;

use App\Models\PackageRelease;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PackageService
{
    public static function isSafePublicHttpsUrl(string $url): bool
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $parts = parse_url($url);
        if (! is_array($parts)) {
            return false;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = (string) ($parts['host'] ?? '');

        if ($scheme !== 'https' || $host === '') {
            return false;
        }

        if (isset($parts['user']) || isset($parts['pass'])) {
            return false;
        }

        $normalizedHost = strtolower($host);
        if (in_array($normalizedHost, ['localhost', '127.0.0.1', '::1'], true)) {
            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $isPublicIp = filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);

            return $isPublicIp !== false;
        }

        return true;
    }

    /**
     * Upload a new package release
     */
    public static function uploadPackage(
        string $version,
        string $releaseChannel,
        string $downloadUrl,
        ?string $changelog = null,
        ?string $virusDetectionLink = null
    ): PackageRelease {
        // Validate version format (YY.M.N[-beta])
        if (! self::isValidTimelineVersion($version)) {
            throw ValidationException::withMessages([
                'version' => 'Invalid version format. Must follow YY.M.N naming style (e.g., 26.3.12 or 26.3.12-beta).',
            ]);
        }

        // Check if version already exists
        if (PackageRelease::where('version', $version)->exists()) {
            throw ValidationException::withMessages([
                'version' => 'This version already exists.',
            ]);
        }

        if (! self::isSafePublicHttpsUrl($downloadUrl)) {
            throw ValidationException::withMessages([
                'download_url' => 'Invalid download URL format.',
            ]);
        }

        if ($virusDetectionLink !== null && ! self::isSafePublicHttpsUrl($virusDetectionLink)) {
            throw ValidationException::withMessages([
                'virus_detection_url' => 'Invalid virus detection URL format.',
            ]);
        }

        return PackageRelease::create([
            'version' => $version,
            'release_channel' => $releaseChannel,
            'download_url' => $downloadUrl,
            'virus_detection_url' => $virusDetectionLink,
            'changelog' => $changelog,
        ]);
    }

    /**
     * Validate a generic client version.
     * Accepts historical semantic versions and current YY.M.N[-beta] release versions.
     */
    public static function isValidSemanticVersion(string $version): bool
    {
        if (self::isValidTimelineVersion($version)) {
            return true;
        }

        return preg_match('/^\d+\.\d+\.\d+(?:-[a-zA-Z0-9]+(?:\.[a-zA-Z0-9]+)*)?(?:\+[a-zA-Z0-9]+(?:\.[a-zA-Z0-9]+)*)?$/', $version) === 1;
    }

    /**
     * Validate release timeline version format (YY.M.N[-beta]).
     */
    public static function isValidTimelineVersion(string $version): bool
    {
        return preg_match('/^\d{2}\.\d{1,2}\.\d+(?:-beta)?$/', $version) === 1;
    }

    public static function compareReleaseVersions(string $left, string $right): int
    {
        $leftParts = self::parseReleaseVersion($left);
        $rightParts = self::parseReleaseVersion($right);

        if ($leftParts === null || $rightParts === null) {
            return version_compare($left, $right);
        }

        foreach (['year', 'month', 'sequence'] as $key) {
            if ($leftParts[$key] === $rightParts[$key]) {
                continue;
            }

            return $leftParts[$key] <=> $rightParts[$key];
        }

        return ($leftParts['is_beta'] <=> $rightParts['is_beta']) * -1;
    }

    /**
     * @return array{year:int, month:int, sequence:int, is_beta:int}|null
     */
    private static function parseReleaseVersion(string $version): ?array
    {
        if (! preg_match('/^(\d{2})\.(\d{1,2})\.(\d+)(-beta)?$/', $version, $matches)) {
            return null;
        }

        return [
            'year' => (int) $matches[1],
            'month' => (int) $matches[2],
            'sequence' => (int) $matches[3],
            'is_beta' => isset($matches[4]) && $matches[4] !== '' ? 1 : 0,
        ];
    }

    /**
     * Get latest package release
     */
    public static function getLatestRelease(string $channel = 'stable'): ?PackageRelease
    {
        $releases = self::getAllReleases($channel);

        if ($releases->isEmpty()) {
            return null;
        }

        return $releases->first();
    }

    /**
     * Get all package releases
     */
    public static function getAllReleases(?string $channel = null): EloquentCollection
    {
        $query = PackageRelease::query();

        if ($channel) {
            $query->where('release_channel', $channel);
        }

        /** @var EloquentCollection<int, PackageRelease> $releases */
        $releases = $query->get();

        /** @var EloquentCollection<int, PackageRelease> $sorted */
        $sorted = $releases
            ->sort(function (PackageRelease $left, PackageRelease $right): int {
                $versionComparison = self::compareReleaseVersions($right->version, $left->version);
                if ($versionComparison !== 0) {
                    return $versionComparison;
                }

                return $right->id <=> $left->id;
            })
            ->values();

        return $sorted;
    }

    public static function getPaginatedReleases(?string $channel = null, int $perPage = 15, ?int $page = null): LengthAwarePaginator
    {
        $releases = self::getAllReleases($channel);
        $currentPage = $page ?? \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $items = $releases->forPage($currentPage, $perPage)->values();

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $releases->count(),
            $perPage,
            $currentPage,
            [
                'path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );
    }

    /**
     * Get package release by version
     */
    public static function getReleaseByVersion(string $version): ?PackageRelease
    {
        return PackageRelease::where('version', $version)->first();
    }

    /**
     * Delete a package release
     */
    public static function deleteRelease(PackageRelease $release): bool
    {
        // Optionally delete the actual file
        // Storage::delete($release->download_url);

        return $release->delete();
    }

    /**
     * Update package release changelog
     */
    public static function updateChangelog(PackageRelease $release, string $changelog): bool
    {
        $release->changelog = $changelog;

        return $release->save();
    }

    /**
     * Get download URL for a package
     */
    public static function getDownloadUrl(PackageRelease $release): string
    {
        return $release->download_url;
    }

    /**
     * Format file size for display
     */
    public static function formatFileSize(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2).' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2).' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2).' KB';
        } else {
            return $bytes.' bytes';
        }
    }

    /**
     * Get package statistics
     */
    public static function getPackageStatistics(): array
    {
        $totalReleases = PackageRelease::count();
        $stableReleases = PackageRelease::where('release_channel', 'stable')->count();
        $devReleases = PackageRelease::where('release_channel', 'dev')->count();

        return [
            'total_releases' => $totalReleases,
            'stable_releases' => $stableReleases,
            'dev_releases' => $devReleases,
            'latest_stable' => self::getLatestRelease('stable'),
            'latest_dev' => self::getLatestRelease('dev'),
        ];
    }
}
