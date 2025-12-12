<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class DeviceBinding extends Model
{
    protected $table = 'device_bindings';
    
    protected $fillable = [
        'license_id',
        'account_id',
        'account_device_id',
        'hwid_hash',
        'ip_address',
        'user_agent',
        'user_agent_hash',
        'country_code',
        'is_active',
        'binding_type',
        'regen_count_at_binding',
        'unbound_at',
        'unbind_reason',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'regen_count_at_binding' => 'integer',
        'unbound_at' => 'datetime',
    ];

    public const BINDING_INITIAL = 'initial';
    public const BINDING_RESET = 'reset';
    public const BINDING_AUTO = 'auto';
    public const BINDING_MANUAL = 'manual';

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function accountDevice(): BelongsTo
    {
        return $this->belongsTo(AccountDevice::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForLicense(Builder $query, int $licenseId): Builder
    {
        return $query->where('license_id', $licenseId);
    }

    public function unbind(string $reason = 'manual'): void
    {
        $this->update([
            'is_active' => false,
            'unbound_at' => now(),
            'unbind_reason' => $reason,
        ]);
    }

    public function isExpired(): bool
    {
        // Device bindings don't expire by time, only by manual unbinding
        return !$this->is_active;
    }
}
