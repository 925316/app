<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
class ClientSession extends Model
{
    protected $table = 'sessions';
    
    protected $fillable = [
        'session_token',
        'account_id',
        'license_id',
        'hwid_hash',
        'ip_address',
        'user_agent',
        'client_version',
        'language',
        'session_data',
        'last_heartbeat_at',
        'expires_at',
        'terminated_at',
        'termination_reason',
    ];

    protected $casts = [
        'session_data' => 'array',
        'last_heartbeat_at' => 'datetime',
        'expires_at' => 'datetime',
        'terminated_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('terminated_at')
            ->where('expires_at', '>', now());
    }

    public function scopeForAccount(Builder $query, int $accountId): Builder
    {
        return $query->where('account_id', $accountId);
    }

    public function scopeForLicense(Builder $query, int $licenseId): Builder
    {
        return $query->where('license_id', $licenseId);
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expires_at', '<=', now());
    }

    public function isActive(): bool
    {
        return !$this->terminated_at && $this->expires_at->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function terminate(string $reason = 'manual'): void
    {
        $this->update([
            'terminated_at' => now(),
            'termination_reason' => $reason,
        ]);
    }

    public function renew(int $minutes = 60): void
    {
        $this->update([
            'expires_at' => now()->addMinutes($minutes),
            'last_heartbeat_at' => now(),
        ]);
    }

    public function getSessionDataAttribute($value)
    {
        return is_array($value) ? $value : json_decode($value, true) ?? [];
    }
}
