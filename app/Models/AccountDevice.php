<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountDevice extends Model
{
    use HasFactory;

    protected $table = 'account_devices';

    protected $fillable = [
        'account_id',
        'hwid_hash',
        'user_agent',
        'ip_address',
        'country_code',
        'device_fingerprint',
        'first_seen_at',
        'last_seen_at',
        'bound_at',
        'unbound_at',
    ];


    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'device_fingerprint' => 'array',
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'bound_at' => 'datetime',
            'unbound_at' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
