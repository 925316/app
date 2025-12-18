<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IpRateLimit extends Model
{
    use HasFactory;

    protected $table = 'ip_rate_limits';

    protected $fillable = [
        'ip_address',
        'endpoint',
        'request_count',
        'last_request_at',
        'is_blocked',
        'blocked_until',
        'block_reason',
    ];

    /**
     * Check if the IP is restricted
     */
    public static function isBlocked(string $ipAddress, string $endpoint = null): bool
    {
        $query = self::where('ip_address', $ipAddress)
            ->where('is_blocked', true);

        if ($endpoint) {
            $query->where('endpoint', $endpoint);
        }

        $record = $query->first();

        if (!$record) {
            return false;
        }

        // Check whether the blockade has expired
        if ($record->blocked_until && $record->blocked_until->isPast()) {
            $record->update([
                'is_blocked' => false,
                'blocked_until' => null,
                'block_reason' => null,
            ]);
            return false;
        }

        return true;
    }

    /**
     * Record request
     */
    public static function recordRequest(string $ipAddress, string $endpoint): self
    {
        $record = self::where('ip_address', $ipAddress)
            ->where('endpoint', $endpoint)
            ->first();

        if ($record) {
            $record->update([
                'request_count' => $record->request_count + 1,
                'last_request_at' => now(),
            ]);
        } else {
            $record = self::create([
                'ip_address' => $ipAddress,
                'endpoint' => $endpoint,
                'request_count' => 1,
                'last_request_at' => now(),
                'is_blocked' => false,
            ]);
        }

        return $record;
    }

    /**
     * Check if the speed limit has been exceeded
     */
    public static function checkRateLimit(string $ipAddress, string $endpoint, int $limit = 60, int $minutes = 1): bool
    {
        $record = self::recordRequest($ipAddress, $endpoint);

        // Check the number of requests within the time window
        $windowStart = now()->subMinutes($minutes);
        $recentRequests = self::where('ip_address', $ipAddress)
            ->where('endpoint', $endpoint)
            ->where('last_request_at', '>=', $windowStart)
            ->sum('request_count');

        return $recentRequests <= $limit;
    }

    /**
     * Block IP
     */
    public static function blockIp(string $ipAddress, string $endpoint, int $minutes = 60, string $reason = null): bool
    {
        $record = self::where('ip_address', $ipAddress)
            ->where('endpoint', $endpoint)
            ->first();

        if ($record) {
            return $record->update([
                'is_blocked' => true,
                'blocked_until' => now()->addMinutes($minutes),
                'block_reason' => $reason,
            ]);
        }

        return self::create([
            'ip_address' => $ipAddress,
            'endpoint' => $endpoint,
            'is_blocked' => true,
            'blocked_until' => now()->addMinutes($minutes),
            'block_reason' => $reason,
            'last_request_at' => now(),
            'request_count' => 1,
        ])->exists;
    }

    /**
     * Lift the IP block
     */
    public static function unblockIp(string $ipAddress, string $endpoint = null): bool
    {
        $query = self::where('ip_address', $ipAddress)
            ->where('is_blocked', true);

        if ($endpoint) {
            $query->where('endpoint', $endpoint);
        }

        return $query->update([
            'is_blocked' => false,
            'blocked_until' => null,
            'block_reason' => null,
        ]);
    }

    /**
     * Clear the old non-blocking records
     */
    public static function cleanupOldRecords(int $hours = 24): int
    {
        return self::where('is_blocked', false)
            ->where('last_request_at', '<', now()->subHours($hours))
            ->delete();
    }

    /**
     * Statistics of requests for obtaining IP addresses
     */
    public static function getIpStats(string $ipAddress, string $endpoint = null): array
    {
        $query = self::where('ip_address', $ipAddress);

        if ($endpoint) {
            $query->where('endpoint', $endpoint);
        }

        $records = $query->get();

        return [
            'total_requests' => $records->sum('request_count'),
            'blocked_count' => $records->where('is_blocked', true)->count(),
            'last_request' => $records->max('last_request_at'),
            'endpoints' => $records->groupBy('endpoint')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total_requests' => $group->sum('request_count'),
                    'blocked' => $group->where('is_blocked', true)->count() > 0,
                ];
            }),
        ];
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'request_count' => 'integer',
            'last_request_at' => 'datetime',
            'is_blocked' => 'boolean',
            'blocked_until' => 'datetime',
        ];
    }
}
