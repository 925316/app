<?php

namespace App\Models;

use App\Enums\EventType;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
     * Get the attributes that should be cast.
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
     * Get the IP address in a formatted way.
     */
    protected function ipAddress(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? inet_ntop($value) : null,
            set: fn (?string $value) => $value ? inet_pton($value) : null,
        );
    }

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
     * Scope a query to only include events of a specific enum type.
     */
    public function scopeOfEventType($query, EventType $eventType)
    {
        return $query->where('event_type', $eventType->value);
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
     * Scope a query to only include events of a specific category.
     */
    public function scopeOfCategory($query, string $category)
    {
        $eventTypes = EventType::getCategoryEvents($category);
        $values = array_map(fn ($type) => $type->value, $eventTypes);

        return $query->whereIn('event_type', $values);
    }

    /**
     * Get the event level as a human-readable string.
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
     */
    public function getTypeLabelAttribute(): string
    {
        $eventType = EventType::tryFrom($this->event_type);

        if ($eventType) {
            return $eventType->label();
        }

        return ucfirst(str_replace('.', ' ', $this->event_type));
    }

    /**
     * Get the event type as an enum instance.
     */
    public function getEventTypeEnumAttribute(): ?EventType
    {
        return EventType::tryFrom($this->event_type);
    }

    /**
     * Get the event category.
     */
    public function getCategoryAttribute(): ?string
    {
        $eventType = EventType::tryFrom($this->event_type);

        if ($eventType) {
            return $eventType->category();
        }

        $parts = explode('.', $this->event_type, 2);

        return $parts[0] ?? null;
    }

    /**
     * Get the event action.
     */
    public function getActionAttribute(): ?string
    {
        $eventType = EventType::tryFrom($this->event_type);

        if ($eventType) {
            return $eventType->action();
        }

        $parts = explode('.', $this->event_type, 2);

        return $parts[1] ?? null;
    }

    /**
     * Check if this is an informational event.
     */
    public function getIsInfoAttribute(): bool
    {
        return $this->event_level === self::LEVEL_INFO;
    }

    /**
     * Check if this is a warning event.
     */
    public function getIsWarningAttribute(): bool
    {
        return $this->event_level === self::LEVEL_WARN;
    }

    /**
     * Check if this is an error event.
     */
    public function getIsErrorAttribute(): bool
    {
        return $this->event_level === self::LEVEL_ERROR;
    }

    /**
     * Get the IP address in a safe format.
     */
    public function getSafeIpAttribute(): ?string
    {
        if (! $this->ip_address) {
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

    /**
     * Log a new event.
     */
    public static function log(
        EventType $eventType,
        int $level = self::LEVEL_INFO,
        array $data = []
    ): EventLog {
        return self::create([
            'event_type' => $eventType->value,
            'event_level' => $level,
            'account_id' => $data['account_id'] ?? null,
            'license_id' => $data['license_id'] ?? null,
            'ip_address' => $data['ip_address'] ?? null,
            'actor_id' => $data['actor_id'] ?? null,
            'details' => $data['details'] ?? [],
        ]);
    }

    /**
     * Log an info level event.
     */
    public static function info(EventType $eventType, array $data = []): EventLog
    {
        return self::log($eventType, self::LEVEL_INFO, $data);
    }

    /**
     * Log a warning level event.
     */
    public static function warning(EventType $eventType, array $data = []): EventLog
    {
        return self::log($eventType, self::LEVEL_WARN, $data);
    }

    /**
     * Log an error level event.
     */
    public static function error(EventType $eventType, array $data = []): EventLog
    {
        return self::log($eventType, self::LEVEL_ERROR, $data);
    }
}
