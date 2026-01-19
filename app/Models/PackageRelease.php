<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageRelease extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'version',
        'release_channel',
        'download_url',
        'virus_detection_url',
        'changelog',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'release_channel' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Available release channels.
     *
     * @var array<string>
     */
    public const RELEASE_CHANNELS = [
        'stable',
        'dev',
    ];

    /**
     * Scope a query to only include stable releases.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStable($query)
    {
        return $query->where('release_channel', 'stable');
    }

    /**
     * Scope a query to only include development releases.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDev($query)
    {
        return $query->where('release_channel', 'dev');
    }

    /**
     * Scope a query to order by latest version.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLatestVersion($query)
    {
        return $query->orderByRaw('CAST(SUBSTRING_INDEX(version, ".", 1) AS UNSIGNED) DESC')
            ->orderByRaw('CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(version, ".", 2), ".", -1) AS UNSIGNED) DESC')
            ->orderByRaw('CAST(SUBSTRING_INDEX(version, ".", -1) AS UNSIGNED) DESC');
    }

    /**
     * Check if the release is stable.
     */
    public function getIsStableAttribute(): bool
    {
        return $this->release_channel === 'stable';
    }

    /**
     * Check if the release is a development release.
     */
    public function getIsDevAttribute(): bool
    {
        return $this->release_channel === 'dev';
    }

    /**
     * Get the major version number.
     */
    public function getMajorVersionAttribute(): ?string
    {
        if (preg_match('/^(\d+)\./', $this->version, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Get the minor version number.
     */
    public function getMinorVersionAttribute(): ?string
    {
        if (preg_match('/^\d+\.(\d+)/', $this->version, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Get the patch version number.
     */
    public function getPatchVersionAttribute(): ?string
    {
        if (preg_match('/^\d+\.\d+\.(\d+)/', $this->version, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
