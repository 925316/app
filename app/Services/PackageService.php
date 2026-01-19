<?php

namespace App\Services;

use App\Models\PackageRelease;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PackageService
{
    /**
     * Upload a new package release
     */
    public static function uploadPackage(
        string $version,
        string $releaseChannel,
        string $filePath,
        ?string $changelog = null
    ): PackageRelease {
        // Validate version format (semantic versioning)
        if (! self::isValidSemanticVersion($version)) {
            throw ValidationException::withMessages([
                'version' => 'Invalid version format. Must follow semantic versioning (e.g., 1.0.0).',
            ]);
        }

        // Check if version already exists
        if (PackageRelease::where('version', $version)->exists()) {
            throw ValidationException::withMessages([
                'version' => 'This version already exists.',
            ]);
        }

        // Validate file exists
        if (! Storage::exists($filePath)) {
            throw ValidationException::withMessages([
                'file' => 'File does not exist.',
            ]);
        }

        // Calculate checksum
        $checksum = hash_file('sha256', Storage::path($filePath));

        return PackageRelease::create([
            'version' => $version,
            'release_channel' => $releaseChannel,
            'download_url' => $filePath,
            'checksum_sha256' => $checksum,
            'changelog' => $changelog,
        ]);
    }

    /**
     * Validate semantic version format
     */
    public static function isValidSemanticVersion(string $version): bool
    {
        return preg_match('/^\d+\.\d+\.\d+(?:-[a-zA-Z0-9]+(?:\.[a-zA-Z0-9]+)*)?(?:\+[a-zA-Z0-9]+(?:\.[a-zA-Z0-9]+)*)?$/', $version) === 1;
    }

    /**
     * Get latest package release
     */
    public static function getLatestRelease(string $channel = 'stable'): ?PackageRelease
    {
        return PackageRelease::where('release_channel', $channel)
            ->orderBy('version', 'desc')
            ->first();
    }

    /**
     * Get all package releases
     */
    public static function getAllReleases(?string $channel = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = PackageRelease::orderBy('version', 'desc');

        if ($channel) {
            $query->where('release_channel', $channel);
        }

        return $query->get();
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
        return Storage::url($release->download_url);
    }

    /**
     * Verify package checksum
     */
    public static function verifyChecksum(PackageRelease $release): bool
    {
        if (! $release->download_url || ! Storage::exists($release->download_url)) {
            return false;
        }

        $currentChecksum = hash_file('sha256', Storage::path($release->download_url));

        return $currentChecksum === $release->checksum_sha256;
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
