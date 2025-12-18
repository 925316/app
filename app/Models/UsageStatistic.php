<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsageStatistic extends Model
{
    use HasFactory;

    protected $table = 'usage_statistics';

    protected $fillable = [
        'stat_type',
        'stat_key',
        'stat_value',
    ];

    const TYPE_GLOBAL = 0;
    const TYPE_USER = 1;
    const TYPE_LICENSE = 2;
    const TYPE_SERVER = 3;

    /**
     * Obtain statistical values
     */
    public static function getValue(int $type, string $key): float
    {
        $stat = self::where('stat_type', $type)
            ->where('stat_key', $key)
            ->first();

        return $stat ? (float) $stat->stat_value : 0;
    }

    /**
     * Set statistical values
     */
    public static function setValue(int $type, string $key, float $value): bool
    {
        return self::updateOrCreate(
            [
                'stat_type' => $type,
                'stat_key' => $key,
            ],
            [
                'stat_value' => $value,
            ]
        )->exists;
    }

    /**
     * Increase the statistical value
     */
    public static function incrementValue(int $type, string $key, float $increment = 1): bool
    {
        $stat = self::where('stat_type', $type)
            ->where('stat_key', $key)
            ->first();

        if ($stat) {
            $stat->stat_value += $increment;
            return $stat->save();
        }

        return self::create([
            'stat_type' => $type,
            'stat_key' => $key,
            'stat_value' => $increment,
        ])->exists;
    }

    /**
     * Obtain global statistics
     */
    public static function getGlobalStats(): array
    {
        return self::where('stat_type', self::TYPE_GLOBAL)
            ->pluck('stat_value', 'stat_key')
            ->toArray();
    }

    /**
     * Obtain user statistics
     */
    public static function getUserStats(int $accountId): array
    {
        return self::where('stat_type', self::TYPE_USER)
            ->where('stat_key', 'like', "user_{$accountId}_%")
            ->pluck('stat_value', 'stat_key')
            ->toArray();
    }

    /**
     * Formatting duration
     */
    public static function formatDuration(float $minutes): string
    {
        $years = floor($minutes / (365 * 24 * 60));
        $months = floor(($minutes % (365 * 24 * 60)) / (30 * 24 * 60));
        $days = floor(($minutes % (30 * 24 * 60)) / (24 * 60));
        $hours = floor(($minutes % (24 * 60)) / 60);
        $minutes = floor($minutes % 60);

        $parts = [];
        if ($years > 0) $parts[] = $years . 'y';
        if ($months > 0) $parts[] = $months . 'm';
        if ($days > 0) $parts[] = $days . 'd';
        if ($hours > 0) $parts[] = $hours . 'h';
        if ($minutes > 0) $parts[] = $minutes . 'm';

        return implode(' ', $parts);
    }

    /**
     * Update login statistics
     */
    public static function recordLogin(): void
    {
        self::incrementValue(self::TYPE_GLOBAL, 'total_logins');
    }

    /**
     * Update usage time
     */
    public static function recordUsageTime(float $minutes): void
    {
        self::incrementValue(self::TYPE_GLOBAL, 'total_usage_minutes', $minutes);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'stat_type' => 'integer',
            'stat_value' => 'decimal:2',
        ];
    }
}
