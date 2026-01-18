<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\UsageStatistic
 *
 * @property int $id
 * @property int $stat_type Statistics Type 0=global, 1=user, 2=license, 3=server
 * @property string $stat_key Statistics Key Name
 * @property float $stat_value Statistics Key Value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class UsageStatistic extends Model
{
    use HasFactory;

    /**
     * Statistics type constants
     */
    const TYPE_GLOBAL = 0;

    const TYPE_USER = 1;

    const TYPE_LICENSE = 2;

    const TYPE_SERVER = 3;

    /**
     * Stat key constants for common usage statistics
     */
    const KEY_LOGIN_COUNT = 'login_count';

    const KEY_USAGE_TIME = 'usage_time';

    const KEY_TOTAL_REQUESTS = 'total_requests';

    const KEY_ACTIVE_SESSIONS = 'active_sessions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'stat_type',
        'stat_key',
        'stat_value',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'stat_type' => 'integer',
            'stat_value' => 'float',
        ];
    }

    // ==================== Query Scopes ====================

    /**
     * Scope a query to only include statistics of a specific type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, int $type)
    {
        return $query->where('stat_type', $type);
    }

    /**
     * Scope a query to only include global statistics.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeGlobal($query)
    {
        return $query->where('stat_type', self::TYPE_GLOBAL);
    }

    /**
     * Scope a query to only include user statistics.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUser($query)
    {
        return $query->where('stat_type', self::TYPE_USER);
    }

    /**
     * Scope a query to only include license statistics.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLicense($query)
    {
        return $query->where('stat_type', self::TYPE_LICENSE);
    }

    /**
     * Scope a query to only include server statistics.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeServer($query)
    {
        return $query->where('stat_type', self::TYPE_SERVER);
    }

    /**
     * Scope a query to only include statistics with a specific key.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithKey($query, string $key)
    {
        return $query->where('stat_key', $key);
    }

    /**
     * Scope a query to only include login count statistics.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLoginCount($query)
    {
        return $query->where('stat_key', self::KEY_LOGIN_COUNT);
    }

    /**
     * Scope a query to only include usage time statistics.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUsageTime($query)
    {
        return $query->where('stat_key', self::KEY_USAGE_TIME);
    }

    // ==================== Accessors & Mutators ====================

    /**
     * Get the type name as a string.
     */
    public function getTypeNameAttribute(): string
    {
        return match ($this->stat_type) {
            self::TYPE_GLOBAL => 'global',
            self::TYPE_USER => 'user',
            self::TYPE_LICENSE => 'license',
            self::TYPE_SERVER => 'server',
            default => 'unknown',
        };
    }

    /**
     * Get a human-readable representation of the stat value.
     * This is specifically for usage time formatting.
     */
    public function getFormattedValueAttribute(): string
    {
        // If this is a usage time statistic, format it nicely
        if ($this->stat_key === self::KEY_USAGE_TIME) {
            return $this->formatUsageTime($this->stat_value);
        }

        // For numeric values, format with commas for thousands
        if (is_numeric($this->stat_value)) {
            return number_format($this->stat_value);
        }

        return (string) $this->stat_value;
    }

    /**
     * Get a descriptive label for this statistic.
     */
    public function getLabelAttribute(): string
    {
        $typeLabels = [
            self::TYPE_GLOBAL => 'Global',
            self::TYPE_USER => 'User',
            self::TYPE_LICENSE => 'License',
            self::TYPE_SERVER => 'Server',
        ];

        $keyLabels = [
            self::KEY_LOGIN_COUNT => 'Login Count',
            self::KEY_USAGE_TIME => 'Usage Time',
            self::KEY_TOTAL_REQUESTS => 'Total Requests',
            self::KEY_ACTIVE_SESSIONS => 'Active Sessions',
        ];

        $type = $typeLabels[$this->stat_type] ?? 'Unknown';
        $key = $keyLabels[$this->stat_key] ?? ucwords(str_replace('_', ' ', $this->stat_key));

        return "{$type} {$key}";
    }

    // ==================== Helper Methods ====================

    /**
     * Format usage time from minutes to human-readable format.
     * Example: 26y 4m 13d 20h 32m
     */
    protected function formatUsageTime(float $minutes): string
    {
        $years = floor($minutes / (365 * 24 * 60));
        $minutes -= $years * 365 * 24 * 60;

        $months = floor($minutes / (30 * 24 * 60));
        $minutes -= $months * 30 * 24 * 60;

        $days = floor($minutes / (24 * 60));
        $minutes -= $days * 24 * 60;

        $hours = floor($minutes / 60);
        $minutes -= $hours * 60;

        $parts = [];

        if ($years > 0) {
            $parts[] = $years.'y';
        }
        if ($months > 0) {
            $parts[] = $months.'m';
        }
        if ($days > 0) {
            $parts[] = $days.'d';
        }
        if ($hours > 0) {
            $parts[] = $hours.'h';
        }
        if ($minutes > 0 || empty($parts)) {
            $parts[] = round($minutes).'m';
        }

        return implode(' ', $parts);
    }

    /**
     * Check if this statistic is of a specific type.
     */
    public function isType(int $type): bool
    {
        return $this->stat_type === $type;
    }

    /**
     * Check if this statistic has a specific key.
     */
    public function hasKey(string $key): bool
    {
        return $this->stat_key === $key;
    }
}
