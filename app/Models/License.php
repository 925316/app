<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class License extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'type',
        'used_by',
        'privilege',
        'status',
        'expires_at',
        'activated_at',
        'suspended_at',
        'created_from_ip',
        'notes',
    ];



    const STATUS_UNUSED = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_SUSPENDED = 2;
    const STATUS_EXPIRED = 3;
    const STATUS_UPGRADED = 4;
    const STATUS_REVOKED = 5;

    const PRIVILEGE_BASIC = 1;
    const PRIVILEGE_REGULAR = 2;
    const PRIVILEGE_ULTIMATE = 3;
    const PRIVILEGE_TESTER = 4;
    const PRIVILEGE_STAFF = 5;

    const TYPE_BASE = 1;
    const TYPE_UPGRADE = 2;

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'used_by');
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE && $this->expires_at > now();
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED || $this->expires_at <= now();
    }

    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'activated_at' => 'datetime',
            'suspended_at' => 'datetime',
        ];
    }
}
