<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Account extends Authenticatable // implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'last_login_at',
        'last_ip_address',
        'last_user_agent',
        'hwid_reset_count',
        'hwid_last_reset_at',
        'is_suspended',
        'suspension_reason',
        'suspended_until',
        'email_verified_at',
        'two_factor_confirmed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'email_verified_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'last_login_at' => 'datetime',
            'hwid_last_reset_at' => 'datetime',
            'suspended_until' => 'datetime',
            'is_suspended' => 'boolean',
            'hwid_reset_count' => 'integer',
        ];
    }

    /**
     * Check if the account has been suspended
     */
    public function isSuspended(): bool
    {
        return $this->is_suspended ||
            ($this->suspended_until && $this->suspended_until->isFuture());
    }

    /**
     * Suspend the account
     */
    public function suspend(?string $reason = null, ?\DateTime $until = null): bool
    {
        $this->is_suspended = true;
        $this->suspension_reason = $reason;
        $this->suspended_until = $until;

        return $this->save();
    }

    /**
     * Restore the account
     */
    public function unsuspend(): bool
    {
        $this->is_suspended = false;
        $this->suspension_reason = null;
        $this->suspended_until = null;

        return $this->save();
    }

    /**
     * Record login information
     */
    public function recordLogin(string $ipAddress, string $userAgent): bool
    {
        $this->last_login_at = now();
        $this->last_ip_address = $ipAddress;
        $this->last_user_agent = $userAgent;

        return $this->save();
    }

    /**
     * Get the number of bound devices
     */
    public function getBoundDeviceCount(): int
    {
        return $this->devices()
            ->whereNotNull('bound_at')
            ->whereNull('unbound_at')
            ->count();
    }

    /**
     * Check if the HWID can be reset
     */
    public function canResetHwid(): bool
    {
        $hoursLimit = 72;

        if ($this->hwid_last_reset_at) {
            return $this->hwid_last_reset_at->diffInHours(now()) >= $hoursLimit;
        }

        return true;
    }

    /**
     * Increase the HWID reset count
     */
    public function incrementHwidResetCount(): bool
    {
        $this->hwid_reset_count++;
        $this->hwid_last_reset_at = now();

        return $this->save();
    }

    /**
     * Check user permissions
     */
    public function hasPrivilege(int $requiredPrivilege): bool
    {
        return $this->getPrivilegeLevel() >= $requiredPrivilege;
    }

    /**
     * Get the last login IP in a formatted way.
     */
    protected function lastIpAddress(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? inet_ntop($value) : null,
            set: fn (?string $value) => $value ? inet_pton($value) : null,
        );
    }

    /**
     * Check if the account is currently suspended.
     */
    public function getIsCurrentlySuspendedAttribute(): bool
    {
        if (! $this->is_suspended) {
            return false;
        }

        if ($this->suspended_until && now()->greaterThan($this->suspended_until)) {
            return false;
        }

        return true;
    }

    /**
     * Scope a query to only include active (non-suspended) accounts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_suspended', false)
            ->orWhere(function ($query) {
                $query->where('is_suspended', true)
                    ->where('suspended_until', '<', now());
            });
    }

    /**
     * Scope a query to only include suspended accounts.
     */
    public function scopeSuspended($query)
    {
        return $query->where('is_suspended', true)
            ->where(function ($query) {
                $query->whereNull('suspended_until')
                    ->orWhere('suspended_until', '>', now());
            });
    }

    /**
     * Scope a query to only include verified accounts.
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    /**
     * Scope a query to only include unverified accounts.
     */
    public function scopeUnverified($query)
    {
        return $query->whereNull('email_verified_at');
    }

    /**
     * Scope a query to only include accounts with 2FA enabled.
     */
    public function scopeHasTwoFactorEnabled($query)
    {
        return $query->whereNotNull('two_factor_secret')
            ->whereNotNull('two_factor_confirmed_at');
    }

    /**
     * Scope a query to order by last login time.
     */
    public function scopeOrderByLastLogin($query, $direction = 'desc')
    {
        return $query->orderBy('last_login_at', $direction);
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->username)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function devices(): HasMany
    {
        return $this->hasMany(AccountDevice::class);
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(License::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ClientSession::class);
    }

    public function eventLogs(): HasMany
    {
        return $this->hasMany(EventLog::class, 'account_id');
    }

    public function usageStatistics(): HasMany
    {
        return $this->hasMany(UsageStatistic::class);
    }
}
