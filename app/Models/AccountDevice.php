<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountDevice extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'account_id',
        'hwid_hash',
        'ip_address',
        'country_code',
        'characteristics',
        'first_seen_at',
        'last_seen_at',
        'bound_at',
        'unbound_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'bound_at' => 'datetime',
            'unbound_at' => 'datetime',
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
     * The attributes that should be appended to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'is_bound',
        'is_active',
        'device_summary',
        'bound_duration',
    ];

    /**
     * Get the account that owns the device.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Scope a query to only include currently bound devices.
     */
    public function scopeBound(Builder $query): Builder
    {
        return $query->whereNotNull('bound_at')
            ->whereNull('unbound_at');
    }

    /**
     * Scope a query to only include unbound devices.
     */
    public function scopeUnbound(Builder $query): Builder
    {
        return $query->whereNotNull('unbound_at');
    }

    /**
     * Scope a query to only include active devices (seen within the last 30 days).
     */
    public function scopeActive(Builder $query, int $days = 30): Builder
    {
        return $query->where('last_seen_at', '>=', now()->subDays($days));
    }

    /**
     * Scope a query to order by last seen time (most recent first).
     */
    public function scopeLatestSeen(Builder $query): Builder
    {
        return $query->orderBy('last_seen_at', 'desc');
    }

    /**
     * Determine if the device is currently bound.
     */
    public function isBound(): bool
    {
        return ! is_null($this->bound_at) && is_null($this->unbound_at);
    }

    /**
     * Check if the account has any bound device.
     */
    public static function hasBoundDevice(int $accountId): bool
    {
        return self::where('account_id', $accountId)
            ->whereNotNull('bound_at')
            ->whereNull('unbound_at')
            ->exists();
    }

    /**
     * Get the bound device for a specific account.
     */
    public static function getBoundDevice(int $accountId): ?self
    {
        return self::where('account_id', $accountId)
            ->whereNotNull('bound_at')
            ->whereNull('unbound_at')
            ->first();
    }

    /**
     * Get the device binding status.
     */
    public function getBindingStatusAttribute(): string
    {
        if (is_null($this->bound_at)) {
            return 'never_bound';
        }

        return is_null($this->unbound_at) ? 'bound' : 'unbound';
    }

    /**
     * Get the device age in days.
     */
    public function getDeviceAgeInDaysAttribute(): int
    {
        return $this->first_seen_at->diffInDays(now());
    }

    /**
     * Get the last activity in human readable format.
     */
    public function getLastActivityHumanAttribute(): string
    {
        return $this->last_seen_at->diffForHumans();
    }
}
