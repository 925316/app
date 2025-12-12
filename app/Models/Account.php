<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected $table = 'accounts';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'privilege_level',
        'preferred_language',
        'last_ip_address',
        'last_user_agent',
        'hwid_reset_count',
        'hwid_last_reset_at',
        'suspension_reason',
        'suspended_until',
        'migrated_from',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'suspended_until' => 'datetime',
            'hwid_last_reset_at' => 'datetime',
            'privilege_level' => 'string',
        ];
    }

    /**
     * Determine if the user is suspended.
     */
    public function isSuspended(): bool
    {
        return !is_null($this->suspended_until) && $this->suspended_until->isFuture();
    }

    /**
     * Determine if the user is a VIP.
     */
    public function isVip(): bool
    {
        return $this->privilege_level === 'vip';
    }

    /**
     * Determine if the user is a basic account.
     */
    public function isBasic(): bool
    {
        return $this->privilege_level === 'basic';
    }

    /**
     * Get the licenses associated with the account.
     */
    public function licenses(): HasMany
    {
        return $this->hasMany(License::class, 'account_id');
    }

    /** 
     * Get the devices associated with the account.
     */
    public function devices(): HasMany
    {
        return $this->hasMany(AccountDevice::class, 'account_id');
    }

    /** 
     * Get the active licenses associated with the account.
     */
    public function activeLicenses()
    {
        return $this->licenses()->where('status', 'active');
    }
}
