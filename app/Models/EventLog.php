<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventLog extends Model
{
    use HasFactory;

    protected $table = 'event_logs';

    protected $fillable = [
        'event_type',
        'event_level',
        'account_id',
        'license_id',
        'ip_address',
        'actor_id',
        'details',
    ];

    const LEVEL_INFO = 0;
    const LEVEL_WARNING = 1;
    const LEVEL_ERROR = 2;

    const TYPE_ACCOUNT_ACTIVATED = 'account.activated';
    const TYPE_DEVICE_BOUND = 'device.bound';
    const TYPE_DEVICE_UNBOUND = 'device.unbound';
    const TYPE_LOGIN_ANOMALY = 'login.anomaly';
    const TYPE_ACCOUNT_SUSPENDED = 'account.suspended';

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'actor_id');
    }


    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'details' => 'array',
            'created_at' => 'datetime',
        ];
    }
}
