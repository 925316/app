<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class EventLog extends Model
{
    protected $table = 'event_logs';

    protected $fillable = [
        'event_type',
        'event_subtype',
        'account_id',
        'license_id',
        'performed_by_id',
        'ip_address',
        'user_agent',
        'user_agent_hash',
        'risk_score',
        'details',
        'metadata',
    ];

    protected $casts = [
        'risk_score' => 'integer',
        'details' => 'array',
        'metadata' => 'array',
    ];

    public const EVENT_CATEGORY_ACCOUNT = 'account';
    public const EVENT_CATEGORY_LICENSE = 'license';
    public const EVENT_CATEGORY_DEVICE = 'device';
    public const EVENT_CATEGORY_SECURITY = 'security';
    public const EVENT_CATEGORY_ADMIN = 'admin';
    public const EVENT_CATEGORY_SYSTEM = 'system';

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'performed_by_id');
    }

    public function scopeForAccount(Builder $query, int $accountId): Builder
    {
        return $query->where('account_id', $accountId);
    }

    public function scopeForLicense(Builder $query, int $licenseId): Builder
    {
        return $query->where('license_id', $licenseId);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('event_type', $type);
    }

    public function isHighRisk(): bool
    {
        return $this->risk_score && $this->risk_score > 70;
    }

    public function isSystemEvent(): bool
    {
        return strpos($this->event_type, self::EVENT_CATEGORY_SYSTEM . '.') === 0;
    }

    public function getDetailsAttribute($value)
    {
        return is_array($value) ? $value : json_decode($value, true) ?? [];
    }
}
