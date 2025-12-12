<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class License extends Model
{
    protected $table = 'licenses';

    protected $fillable = [
        'license_key',
        'account_id',
        'license_type',
        'license_tier',
        'status',
        'device_binding_id',
        'account_device_id',
        'hwid_bound_at',
        'hwid_reset_at',
        'activation_key_used',
        'expires_at',
        'activated_at',
        'suspended_at',
        'suspension_reason',
        'auto_suspend_reason',
        'upgraded_to_id',
        'created_from_ip',
        'total_activations',
        'notes',
    ];

    protected $casts = [
        'license_tier' => 'integer',
        'total_activations' => 'integer',
        'expires_at' => 'datetime',
        'activated_at' => 'datetime',
        'suspended_at' => 'datetime',
        'hwid_bound_at' => 'datetime',
        'hwid_reset_at' => 'datetime',
    ];

    public const STATUS_UNUSED = 'unused';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_UPGRADED = 'upgraded';
    public const STATUS_REVOKED = 'revoked';

    public const TYPE_BASIC = 'basic';
    public const TYPE_VIP = 'vip';

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function accountDevice(): BelongsTo
    {
        return $this->belongsTo(AccountDevice::class);
    }

    public function deviceBinding(): BelongsTo
    {
        return $this->belongsTo(DeviceBinding::class);
    }

    public function upgradedTo(): BelongsTo
    {
        return $this->belongsTo(License::class, 'upgraded_to_id');
    }

    public function upgradedFrom(): HasOne
    {
        return $this->hasOne(License::class, 'upgraded_to_id');
    }

    public function activationKeys(): HasMany
    {
        return $this->hasMany(ActivationKey::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function deviceBindings(): HasMany
    {
        return $this->hasMany(DeviceBinding::class);
    }

    public function heartbeatLogs(): HasMany
    {
        return $this->hasMany(HeartbeatLog::class);
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED ||
            ($this->expires_at && $this->expires_at->isPast());
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE && !$this->isExpired();
    }

    public function canUpgrade(): bool
    {
        return $this->isActive() && $this->license_type === self::TYPE_BASIC;
    }

    public function daysRemaining(): ?int
    {
        if (!$this->expires_at) {
            return null;
        }

        return now()->diffInDays($this->expires_at, false);
    }
}
