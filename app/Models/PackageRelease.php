<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class PackageRelease extends Model
{
    protected $table = 'package_releases';
    
    protected $fillable = [
        'version',
        'release_channel',
        'min_license_tier',
        'download_url',
        'checksum_sha256',
        'file_size_bytes',
        'changelog',
        'is_critical',
        'is_force_update',
        'release_date',
        'end_of_support',
        'download_count',
    ];

    protected $casts = [
        'min_license_tier' => 'integer',
        'file_size_bytes' => 'integer',
        'download_count' => 'integer',
        'is_critical' => 'boolean',
        'is_force_update' => 'boolean',
        'release_date' => 'datetime',
        'end_of_support' => 'datetime',
    ];

    public const CHANNEL_STABLE = 'stable';
    public const CHANNEL_BETA = 'beta';
    public const CHANNEL_ALPHA = 'alpha';
    public const CHANNEL_DEV = 'dev';

    public function scopeStable(Builder $query): Builder
    {
        return $query->where('release_channel', self::CHANNEL_STABLE);
    }

    public function scopeForLicenseTier(Builder $query, int $tier): Builder
    {
        return $query->where('min_license_tier', '<=', $tier);
    }

    public function scopeCriticalUpdates(Builder $query): Builder
    {
        return $query->where('is_critical', true);
    }

    public function scopeForceUpdates(Builder $query): Builder
    {
        return $query->where('is_force_update', true);
    }

    public function scopeLatestVersion(Builder $query): Builder
    {
        return $query->orderByDesc('release_date')->limit(1);
    }

    public function isSupported(): bool
    {
        if (!$this->end_of_support) {
            return true;
        }

        return $this->end_of_support->isFuture();
    }

    public function isAvailableForTier(int $tier): bool
    {
        return $tier >= $this->min_license_tier;
    }

    public function incrementDownloadCount(): void
    {
        $this->increment('download_count');
    }

    public function getFileSizeFormatted(): string
    {
        if (!$this->file_size_bytes) {
            return 'Unknown';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = $this->file_size_bytes;
        $unit = 0;

        while ($bytes >= 1024 && $unit < count($units) - 1) {
            $bytes /= 1024;
            $unit++;
        }

        return round($bytes, 2) . ' ' . $units[$unit];
    }
}
