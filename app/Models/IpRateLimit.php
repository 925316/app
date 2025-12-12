<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class IpRateLimit extends Model
{
    protected $table = 'ip_rate_limits';
    
    protected $fillable = [
        'ip_address',
        'endpoint',
        'request_count',
        'first_request_at',
        'last_request_at',
        'is_blocked',
        'blocked_until',
        'block_reason',
    ];

    protected $casts = [
        'request_count' => 'integer',
        'is_blocked' => 'boolean',
        'first_request_at' => 'datetime',
        'last_request_at' => 'datetime',
        'blocked_until' => 'datetime',
    ];

    public function scopeForIp(Builder $query, string $ipAddress): Builder
    {
        return $query->where('ip_address', $ipAddress);
    }

    public function scopeForEndpoint(Builder $query, string $endpoint): Builder
    {
        return $query->where('endpoint', $endpoint);
    }

    public function scopeBlocked(Builder $query): Builder
    {
        return $query->where('is_blocked', true)
            ->where(function ($q) {
                $q->whereNull('blocked_until')
                  ->orWhere('blocked_until', '>', now());
            });
    }

    public function scopeExpiredBlocks(Builder $query): Builder
    {
        return $query->where('is_blocked', true)
            ->where('blocked_until', '<=', now());
    }

    public function scopeOldRecords(Builder $query, int $hours = 24): Builder
    {
        return $query->where('last_request_at', '<=', now()->subHours($hours))
            ->where('is_blocked', false);
    }

    public function isCurrentlyBlocked(): bool
    {
        return $this->is_blocked && 
               ($this->blocked_until === null || $this->blocked_until->isFuture());
    }

    public function incrementRequestCount(): void
    {
        $this->increment('request_count');
        $this->update(['last_request_at' => now()]);
    }

    public function block(string $reason, ?\DateTime $until = null): void
    {
        $this->update([
            'is_blocked' => true,
            'blocked_until' => $until,
            'block_reason' => $reason,
        ]);
    }

    public function unblock(): void
    {
        $this->update([
            'is_blocked' => false,
            'blocked_until' => null,
            'block_reason' => null,
        ]);
    }

    public function shouldBeCleaned(): bool
    {
        return !$this->is_blocked && $this->last_request_at->diffInHours(now()) > 24;
    }
}
