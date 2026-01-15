<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;

class License extends Model
{
    use HasFactory;

    const STATUS_UNUSED = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_SUSPENDED = 2;
    const STATUS_EXPIRED = 3;
    const STATUS_UPGRADED = 4;
    const STATUS_REVOKED = 5;

    const TYPE_BASE = 1;
    const TYPE_UPGRADE = 2;

    const PRIVILEGE_DEFAULT = 0;
    const PRIVILEGE_BASIC = 1;
    const PRIVILEGE_REGULAR = 2;
    const PRIVILEGE_ULTIMATE = 3;
    const PRIVILEGE_TESTER = 4;
    const PRIVILEGE_STAFF = 5;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'type',
        'privilege',
        'status',
        'used_by',
        'expires_at',
        'activated_at',
        'suspended_at',
        'created_from_ip',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => 'integer',
            'privilege' => 'integer',
            'status' => 'integer',
            'expires_at' => 'datetime',
            'activated_at' => 'datetime',
            'suspended_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the account that owns the license.
     */
    public function account()
    {
        return $this->belongsTo(Account::class, 'used_by');
    }

    /**
     * Scope a query to only include active licenses.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', 1);
    }

    /**
     * Scope a query to only include unused licenses.
     */
    public function scopeUnused(Builder $query): void
    {
        $query->where('status', 0);
    }

    /**
     * Scope a query to only include suspended licenses.
     */
    public function scopeSuspended(Builder $query): void
    {
        $query->where('status', 2);
    }

    /**
     * Scope a query to only include expired licenses.
     */
    public function scopeExpired(Builder $query): void
    {
        $query->where('status', 3);
    }

    /**
     * Scope a query to only include licenses of a specific type.
     */
    public function scopeType(Builder $query, int $type): void
    {
        $query->where('type', $type);
    }

    /**
     * Scope a query to only include licenses with specific privilege.
     */
    public function scopePrivilege(Builder $query, int $privilege): void
    {
        $query->where('privilege', $privilege);
    }

    /**
     * Scope a query to only include valid (active and not expired) licenses.
     */
    public function scopeValid(Builder $query): void
    {
        $query->where('status', 1)
              ->where('expires_at', '>', now());
    }

    /**
     * Activate license.
     */
    public function activate(int $accountId, ?string $ip = null): bool
    {
        if ($this->status !== self::STATUS_UNUSED) {
            throw new \LogicException('Only unused licenses can be activated.');
        }

        $this->status = self::STATUS_ACTIVE;
        $this->used_by = $accountId;
        $this->activated_at = now();

        if ($ip) {
            $this->created_from_ip = $ip;
        }

        return $this->save();
    }

    /**
     * Check if license is expired.
     */
    public function isExpired(): bool
    {
        return $this->status === 3 || ($this->expires_at && $this->expires_at->isPast());
    }

    /**
     * Check if license is active.
     */
    public function isActive(): bool
    {
        return $this->status === 1 && !$this->isExpired();
    }

    /**
     * Check if license is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->status === 2;
    }

    public function isUnused(): bool
    {
        return $this->status === 0;
    }

    public function isUpgraded(): bool
    {
        return $this->status === self::STATUS_UPGRADED;
    }

    public function isRevoked(): bool
    {
        return $this->status === self::STATUS_REVOKED;
    }

    public function daysUntilExpiry(): int
    {
        if (!$this->expires_at || $this->isExpired()) {
            return 0;
        }

        return now()->diffInDays($this->expires_at, false);
    }

    /**
     * Check if an account has active license.
     * @param int $accountId
     * @return bool
     */
    public static function hasActiveLicense(int $accountId): bool
    {
        return self::where('used_by', $accountId)
            ->where('status', self::STATUS_ACTIVE)
            ->exists();
    }

    /**
     * Get active license for an account.
     * @param int $accountId
     * @return License|null
     */
    public static function getActiveLicense(int $accountId): ?self
    {
        return self::where('used_by', $accountId)
            ->where('status', self::STATUS_ACTIVE)
            ->first();
    }

    /**
     * Get the status as a human-readable string.
     */
    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            0 => 'unused',
            1 => 'active',
            2 => 'suspended',
            3 => 'expired',
            4 => 'upgraded',
            5 => 'revoked',
            default => 'unknown',
        };
    }

    /**
     * Get the type as a human-readable string.
     */
    public function getTypeTextAttribute(): string
    {
        return match($this->type) {
            1 => 'base',
            2 => 'upgrade',
            default => 'unknown',
        };
    }

    /**
     * Get the privilege tier as a human-readable string.
     */
    public function getPrivilegeTextAttribute(): string
    {
        return match($this->privilege) {
            1 => 'basic',
            2 => 'regular',
            3 => 'ultimate',
            4 => 'tester',
            5 => 'staff',
            default => 'unknown',
        };
    }

    /**
     * Interact with the license key.
     */
    protected function key(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => strtoupper($value),
            set: fn (string $value) => strtoupper($value),
        );
    }

    /**
     * Interact with the created_from_ip.
     */
    protected function createdFromIp(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value,
            set: fn (?string $value) => filter_var($value, FILTER_VALIDATE_IP) ? $value : null,
        );
    }
}