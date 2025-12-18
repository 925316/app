<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Account extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'license_id',
        'last_login_at',
        'last_ip_address',
        'last_user_agent',
        'hwid_reset_count',
        'hwid_last_reset_at',
        'is_suspended',
        'suspension_reason',
        'suspended_until',
        'email_verified_at',
        'two_factor_secret',
        'two_factor_recovery_codes',
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
     * The main associated licenses
     */
    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    /**
     * Associated devices
     */
    public function devices(): HasMany
    {
        return $this->hasMany(AccountDevice::class);
    }

    /**
     * All associated licenses
     */
    public function licenses(): HasMany
    {
        return $this->hasMany(License::class, 'used_by');
    }

    /**
     * Associated upgrade records
     */
    public function upgrades(): HasMany
    {
        return $this->hasMany(Upgrade::class);
    }

    /**
     * Associated event logs
     */
    public function eventLogs(): HasMany
    {
        return $this->hasMany(EventLog::class);
    }

    /**
     * Associated client sessions
     */
    public function clientSessions(): HasMany
    {
        return $this->hasMany(ClientSession::class);
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
    public function suspend(string $reason = null, \DateTime $until = null): bool
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
     * Obtain the user permission level
     */
    public function getPrivilegeLevel(): int
    {
        if ($this->license) {
            return $this->license->privilege;
        }

        return License::PRIVILEGE_BASIC;
    }

    /**
     * Check user permissions
     */
    public function hasPrivilege(int $requiredPrivilege): bool
    {
        return $this->getPrivilegeLevel() >= $requiredPrivilege;
    }

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
            'last_login_at' => 'datetime',
            'hwid_last_reset_at' => 'datetime',
            'suspended_until' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'is_suspended' => 'boolean',
        ];
    }
}
