<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ActivationKey extends Model
{
    protected $table = 'activation_keys';

    protected $fillable = [
        'key',
        'key_type',
        'target_license_type',
        'privilege_level',
        'account_id',
        'license_id',
        'used_at',
        'expires_at',
        'use_count',
        'is_revoked',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'privilege_level' => 'integer',
        'use_count' => 'integer',
        'is_revoked' => 'boolean',
        'used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public const TYPE_LICENSE = 'license';
    public const TYPE_UPGRADE = 'upgrade';
    public const TYPE_TOPUP = 'topup';
    public const TYPE_RESET = 'reset';

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'created_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_revoked', false)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function scopeUnused(Builder $query): Builder
    {
        return $query->whereNull('used_at');
    }

    public function scopeLicenseType(Builder $query, string $type): Builder
    {
        return $query->where('target_license_type', $type);
    }

    public function isUsable(): bool
    {
        return !$this->is_revoked &&
            !$this->used_at &&
            (!$this->expires_at || $this->expires_at->isFuture());
    }

    public function markAsUsed(?Account $account = null, ?License $license = null): void
    {
        $this->update([
            'used_at' => now(),
            'account_id' => $account?->id,
            'license_id' => $license?->id,
            'use_count' => $this->use_count + 1,
        ]);
    }
}
