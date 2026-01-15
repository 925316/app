<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventLog extends Model
{
    use HasFactory;

    /**
     * Event Level Constants
     */
    public const LEVEL_INFO = 0;
    public const LEVEL_WARN = 1;
    public const LEVEL_ERROR = 2;

    /**
     * Event Type Constants
     */
    public const TYPE_ACCOUNT_ACTIVATED = 'account.activated';
    public const TYPE_DEVICE_BOUND = 'device.bound';
    public const TYPE_DEVICE_UNBOUND = 'device.unbound';
    public const TYPE_LOGIN_ANOMALY = 'login.anomaly';
    public const TYPE_ACCOUNT_SUSPENDED = 'account.suspended';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'event_type',
        'event_level',
        'account_id',
        'license_id',
        'ip_address',
        'actor_id',
        'details',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'details' => 'array',
            'event_level' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the attributes that should be cast.
     *
     * @deprecated Use the casts() method instead.
     */
    protected $casts = [
        'details' => 'array',
        'event_level' => 'integer',
    ];

    /**
     * Relationship with the account that this event is about.
     */
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    /**
     * Relationship with the license that this event is about.
     */
    public function license()
    {
        return $this->belongsTo(License::class, 'license_id');
    }

    /**
     * Relationship with the actor (account) who performed the operation.
     */
    public function actor()
    {
        return $this->belongsTo(Account::class, 'actor_id');
    }

    /**
     * Scope a query to only include info level events.
     */
    public function scopeInfo($query)
    {
        return $query->where('event_level', self::LEVEL_INFO);
    }

    /**
     * Scope a query to only include warning level events.
     */
    public function scopeWarning($query)
    {
        return $query->where('event_level', self::LEVEL_WARN);
    }

    /**
     * Scope a query to only include error level events.
     */
    public function scopeError($query)
    {
        return $query->where('event_level', self::LEVEL_ERROR);
    }

    /**
     * Scope a query to only include events of a specific type.
     */
    public function scopeOfType($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Scope a query to only include events for a specific account.
     */
    public function scopeForAccount($query, int $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    /**
     * Scope a query to only include events for a specific license.
     */
    public function scopeForLicense($query, int $licenseId)
    {
        return $query->where('license_id', $licenseId);
    }

    /**
     * Scope a query to only include events from a specific actor.
     */
    public function scopeByActor($query, int $actorId)
    {
        return $query->where('actor_id', $actorId);
    }

    /**
     * Scope a query to only include events within a date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include recent events (last 30 days by default).
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Get the event level as a human-readable string.
     *
     * @return string
     */
    public function getLevelTextAttribute(): string
    {
        return match ($this->event_level) {
            self::LEVEL_INFO => 'Info',
            self::LEVEL_WARN => 'Warning',
            self::LEVEL_ERROR => 'Error',
            default => 'Unknown',
        };
    }

    /**
     * Get the event level CSS class for UI display.
     *
     * @return string
     */
    public function getLevelClassAttribute(): string
    {
        return match ($this->event_level) {
            self::LEVEL_INFO => 'info',
            self::LEVEL_WARN => 'warning',
            self::LEVEL_ERROR => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get the event type in a human-readable format.
     *
     * @return string
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->event_type) {
            self::TYPE_ACCOUNT_ACTIVATED => 'Account Activated',
            self::TYPE_DEVICE_BOUND => 'Device Bound',
            self::TYPE_DEVICE_UNBOUND => 'Device Unbound',
            self::TYPE_LOGIN_ANOMALY => 'Login Anomaly',
            self::TYPE_ACCOUNT_SUSPENDED => 'Account Suspended',
            default => ucfirst(str_replace('.', ' ', $this->event_type)),
        };
    }

    /**
     * Check if this is an informational event.
     *
     * @return bool
     */
    public function getIsInfoAttribute(): bool
    {
        return $this->event_level === self::LEVEL_INFO;
    }

    /**
     * Check if this is a warning event.
     *
     * @return bool
     */
    public function getIsWarningAttribute(): bool
    {
        return $this->event_level === self::LEVEL_WARN;
    }

    /**
     * Check if this is an error event.
     *
     * @return bool
     */
    public function getIsErrorAttribute(): bool
    {
        return $this->event_level === self::LEVEL_ERROR;
    }

    /**
     * Get the IP address in a safe format.
     *
     * @return string|null
     */
    public function getSafeIpAttribute(): ?string
    {
        if (!$this->ip_address) {
            return null;
        }

        // Mask the last part of IPv4 addresses for privacy
        if (filter_var($this->ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return preg_replace('/\.\d+$/', '.xxx', $this->ip_address);
        }

        // Mask parts of IPv6 addresses for privacy
        if (filter_var($this->ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $parts = explode(':', $this->ip_address);
            if (count($parts) > 4) {
                $parts = array_slice($parts, 0, 4);
                $parts[] = 'xxxx';
                return implode(':', $parts);
            }
        }

        return $this->ip_address;
    }
}