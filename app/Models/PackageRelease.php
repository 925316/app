<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageRelease extends Model
{
    use HasFactory;

    protected $table = 'package_releases';

    protected $fillable = [
        'version',
        'release_channel',
        'download_url',
        'checksum_sha256',
        'changelog',
        'download_count',
    ];

    const CHANNEL_STABLE = 'stable';
    const CHANNEL_DEV = 'dev';

    /**
     * Check if it is a stable version
     */
    public function isStable(): bool
    {
        return $this->release_channel === self::CHANNEL_STABLE;
    }

    /**
     * Check if it is a dev version
     */
    public function isDev(): bool
    {
        return $this->release_channel === self::CHANNEL_DEV;
    }

    /**
     * Increase the download count
     */
    public function incrementDownloadCount(): void
    {
        $this->increment('download_count');
    }

    /**
     * Obtain the array of semantic version numbers
     */
    public function getVersionParts(): array
    {
        return explode('.', $this->version);
    }

    /**
     * Check if there is an updated version
     */
    public static function hasUpdate(string $currentVersion): bool
    {
        $latest = self::where('release_channel', self::CHANNEL_STABLE)
            ->orderByRaw("CAST(SUBSTRING_INDEX(version, '.', 1) AS UNSIGNED) DESC")
            ->orderByRaw("CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(version, '.', 2), '.', -1) AS UNSIGNED) DESC")
            ->orderByRaw("CAST(SUBSTRING_INDEX(version, '.', -1) AS UNSIGNED) DESC")
            ->first();

        if (!$latest) {
            return false;
        }

        return version_compare($latest->version, $currentVersion, '>');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'download_count' => 'integer',
        ];
    }
}
