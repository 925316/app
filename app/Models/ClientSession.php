<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientSession extends Model
{
    use HasFactory;

    protected $table = 'client_sessions';

    protected $fillable = [
        'session_token',
        'account_id',
        'ip_address',
        'user_agent',
        'client_version',
        'language',
        'session_data',
        'last_heartbeat_at',
    ];

    /**
     * Associated account
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Update the heartbeat time to check if the session has expired (no heartbeat for 30 minutes)
     */
    public function isExpired(): bool
    {
        if (!$this->last_heartbeat_at) {
            return true;
        }

        return $this->last_heartbeat_at->diffInMinutes(now()) > 30;
    }

    /**
     * Update heart rate time
     */
    public function updateHeartbeat(): bool
    {
        $this->last_heartbeat_at = now();
        return $this->save();
    }

    /**
     * Generate a new session token
     */
    public static function generateToken(): string
    {
        return hash('sha512', uniqid('session_', true) . microtime(true) . random_bytes(32));
    }

    /**
     * Obtain session data
     */
    public function getSessionData(string $key = null, $default = null)
    {
        if (!$this->session_data) {
            return $default;
        }

        if ($key === null) {
            return $this->session_data;
        }

        return $this->session_data[$key] ?? $default;
    }

    /**
     * Set session data
     */
    public function setSessionData(string $key, $value): bool
    {
        $data = $this->session_data ?? [];
        $data[$key] = $value;
        $this->session_data = $data;
        return $this->save();
    }

    /**
     * Clear out expired sessions
     */
    public static function cleanupExpired(): int
    {
        return self::where('last_heartbeat_at', '<', now()->subMinutes(30))->delete();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'session_data' => 'array',
            'last_heartbeat_at' => 'datetime',
        ];
    }
}
