<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class AccountDevice extends Model
{
    protected $table = 'account_devices';
    
    protected $fillable = [
        'account_id',
        'hwid_hash',
        'user_agent_hash',
        'ip_address',
        'country_code',
        'device_fingerprint',
        'reputation_score',
        'first_seen_at',
        'last_seen_at',
        'seen_count',
        'is_active',
        'is_trusted',
        'is_suspicious',
        'risk_factors',
    ];

    protected $casts = [
        'device_fingerprint' => 'array',
        'risk_factors' => 'array',
        'reputation_score' => 'integer',
        'seen_count' => 'integer',
        'is_active' => 'boolean',
        'is_trusted' => 'boolean',
        'is_suspicious' => 'boolean',
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(License::class, 'account_device_id');
    }

    //public function deviceBindings(): HasMany
    //{
    //    return $this->hasMany(DeviceBinding::class, 'account_device_id');
    //}

    public function isBanned(): bool
    {
        return $this->reputation_score < 30;
    }

    public function isHighlyTrusted(): bool
    {
        return $this->is_trusted && $this->reputation_score > 80;
    }

    public function incrementSeenCount(): void
    {
        $this->increment('seen_count');
        $this->update(['last_seen_at' => now()]);
    }
}
