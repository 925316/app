<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientSession extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'session_token',
        'account_id',
        'device_id',
        'ip_address',
        'client_version',
        'last_heartbeat_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_heartbeat_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the IP address in a formatted way.
     */
    protected function ipAddress(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? inet_ntop($value) : null,
            set: fn (?string $value) => $value ? inet_pton($value) : null,
        );
    }

    /**
     * Get the account that owns the session.
     */
    public function account(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the device that owns the session.
     */
    public function device(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AccountDevice::class, 'device_id');
    }

    /**
     * Scope a query to only include active sessions (with recent heartbeat).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     */
    public function scopeActive($query, int $minutesThreshold = 5): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereNotNull('last_heartbeat_at')
            ->where('last_heartbeat_at', '>=', now()->subMinutes($minutesThreshold));
    }

    /**
     * Scope a query to only include expired sessions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     */
    public function scopeExpired($query, int $minutesThreshold = 5): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where(function ($q) use ($minutesThreshold) {
            $q->whereNull('last_heartbeat_at')
                ->orWhere('last_heartbeat_at', '<', now()->subMinutes($minutesThreshold));
        });
    }

    /**
     * Scope a query to order by last heartbeat (most recent first).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     */
    public function scopeOrderByRecent($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->orderByDesc('last_heartbeat_at');
    }

    /**
     * Scope a query to filter by account.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     */
    public function scopeForAccount($query, int $accountId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('account_id', $accountId);
    }

    /**
     * Scope a query to filter by device.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     */
    public function scopeForDevice($query, int $deviceId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('device_id', $deviceId);
    }

    /**
     * Check if the session is active.
     */
    public function isActive(int $minutesThreshold = 5): bool
    {
        return $this->last_heartbeat_at &&
               $this->last_heartbeat_at->gte(now()->subMinutes($minutesThreshold));
    }

    /**
     * Get the session age in minutes.
     */
    public function getAgeInMinutesAttribute(): ?float
    {
        if (! $this->created_at) {
            return null;
        }

        return $this->created_at->diffInMinutes(now());
    }

    /**
     * Get the time since last heartbeat in minutes.
     */
    public function getTimeSinceLastHeartbeatAttribute(): ?float
    {
        if (! $this->last_heartbeat_at) {
            return null;
        }

        return $this->last_heartbeat_at->diffInMinutes(now());
    }
}
