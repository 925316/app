<?php

namespace App\Models;

use App\Enums\LicensePrivilege;
use App\Enums\LicenseStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
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
            'privilege' => LicensePrivilege::class,
            'status' => LicenseStatus::class,
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
        $query->where('status', LicenseStatus::ACTIVE->value);
    }

    /**
     * Scope a query to only include unused licenses.
     */
    public function scopeUnused(Builder $query): void
    {
        $query->where('status', LicenseStatus::UNUSED->value);
    }

    /**
     * Scope a query to only include suspended licenses.
     */
    public function scopeSuspended(Builder $query): void
    {
        $query->where('status', LicenseStatus::SUSPENDED->value);
    }

    /**
     * Scope a query to only include expired licenses.
     */
    public function scopeExpired(Builder $query): void
    {
        $query->where('status', LicenseStatus::EXPIRED->value);
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
        $query->where('status', LicenseStatus::ACTIVE->value)
            ->where('expires_at', '>', now());
    }

    /**
     * Scope a query to only include licenses expiring soon (within X days).
     */
    public function scopeExpiringSoon(Builder $query, int $days = 7): void
    {
        $query->where('status', LicenseStatus::ACTIVE->value)
            ->whereBetween('expires_at', [now(), now()->addDays($days)]);
    }

    /**
     * Scope a query to only include licenses that belong to an account.
     */
    public function scopeForAccount(Builder $query, int $accountId): void
    {
        $query->where('used_by', $accountId);
    }

    /**
     * Scope a query to exclude licenses with certain statuses.
     */
    public function scopeExcludingStatuses(Builder $query, array $statuses): void
    {
        $query->whereNotIn('status', $statuses);
    }

    /**
     * Scope a query to only include licenses created within a date range.
     */
    public function scopeCreatedBetween(Builder $query, ?string $start, ?string $end): void
    {
        if ($start) {
            $query->where('created_at', '>=', $start);
        }
        if ($end) {
            $query->where('created_at', '<=', $end);
        }
    }

    /**
     * Activate license.
     */
    public function activate(int $accountId, ?string $ip = null): bool
    {
        if ($this->status !== LicenseStatus::UNUSED) {
            return false;
        }

        $this->status = LicenseStatus::ACTIVE;
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
        return $this->status === LicenseStatus::EXPIRED ||
               ($this->expires_at && $this->expires_at->isPast());
    }

    /**
     * Check if license is active.
     */
    public function isActive(): bool
    {
        return $this->status === LicenseStatus::ACTIVE && ! $this->isExpired();
    }

    /**
     * Check if license is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->status === LicenseStatus::SUSPENDED;
    }

    public function isUnused(): bool
    {
        return $this->status === LicenseStatus::UNUSED;
    }

    public function isUpgraded(): bool
    {
        return $this->status === LicenseStatus::UPGRADED;
    }

    public function isRevoked(): bool
    {
        return $this->status === LicenseStatus::REVOKED;
    }

    public function daysUntilExpiry(): int
    {
        if (! $this->expires_at || $this->isExpired()) {
            return 0;
        }

        return now()->diffInDays($this->expires_at, false);
    }

    /**
     * Check if an account has active license.
     */
    public static function hasActiveLicense(int $accountId): bool
    {
        return self::where('used_by', $accountId)
            ->where('status', LicenseStatus::ACTIVE->value)
            ->exists();
    }

    /**
     * Get active license for an account.
     */
    public static function getActiveLicense(int $accountId): ?self
    {
        return self::where('used_by', $accountId)
            ->where('status', LicenseStatus::ACTIVE->value)
            ->first();
    }

    /**
     * Get the status as a human-readable string.
     */
    public function getStatusTextAttribute(): string
    {
        return $this->status->getLabel();
    }

    /**
     * Get the status color.
     */
    public function getStatusColorAttribute(): string
    {
        return $this->status->getColor();
    }

    /**
     * Get the privilege tier as a human-readable string.
     */
    public function getPrivilegeTextAttribute(): string
    {
        return $this->privilege?->getLabel() ?? 'unknown';
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

    public function canActivate(): bool
    {
        return $this->status?->canActivate() ?? false;
    }

    /**
     * Check if the license can be activated based on privilege level
     */
    public function canActivateByPrivilege(): bool
    {
        // Level 2 (UPGRADE) cannot be activated alone
        if ($this->privilege === LicensePrivilege::UPGRADE) {
            return false;
        }
        
        // Other levels can be activated directly
        return true;
    }
}
