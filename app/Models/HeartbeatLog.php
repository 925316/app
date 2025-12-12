<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class HeartbeatLog extends Model
{
    protected $table = 'heartbeat_logs';
    
    protected $fillable = [
        'license_id',
        'account_id',
        'hwid_hash',
        'session_id',
        'client_version',
        'ip_address',
        'country_code',
        'user_agent',
        'uptime_seconds',
        'memory_usage_mb',
        'is_offline_report',
        'next_heartbeat_expected',
        'session_status',
        'heartbeat_count',
        'avg_heartbeat_interval',
        'missed_heartbeats',
        'received_at',
    ];

    protected $casts = [
        'uptime_seconds' => 'integer',
        'memory_usage_mb' => 'integer',
        'is_offline_report' => 'boolean',
        'heartbeat_count' => 'integer',
        'avg_heartbeat_interval' => 'integer',
        'missed_heartbeats' => 'integer',
        'received_at' => 'datetime',
        'next_heartbeat_expected' => 'datetime',
    ];

    public const SESSION_ACTIVE = 'active';
    public const SESSION_IDLE = 'idle';
    public const SESSION_STALE = 'stale';
    public const SESSION_DEAD = 'dead';

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function scopeActiveSession(Builder $query): Builder
    {
        return $query->where('session_status', self::SESSION_ACTIVE);
    }

    public function scopeForLicense(Builder $query, int $licenseId): Builder
    {
        return $query->where('license_id', $licenseId);
    }

    public function scopeRecent(Builder $query, int $hours = 24): Builder
    {
        return $query->where('received_at', '>=', now()->subHours($hours));
    }

    public function scopeMissedHeartbeats(Builder $query, int $threshold = 3): Builder
    {
        return $query->where('missed_heartbeats', '>=', $threshold);
    }

    public function isStale(): bool
    {
        return $this->session_status === self::SESSION_STALE;
    }

    public function isDead(): bool
    {
        return $this->session_status === self::SESSION_DEAD;
    }

    public function shouldResend(): bool
    {
        return $this->is_offline_report && $this->missed_heartbeats > 0;
    }

    public function getUptimeFormatted(): string
    {
        if (!$this->uptime_seconds) {
            return 'N/A';
        }

        $hours = floor($this->uptime_seconds / 3600);
        $minutes = floor(($this->uptime_seconds % 3600) / 60);
        $seconds = $this->uptime_seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
}
