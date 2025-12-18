<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\EventLog;

class EventLogFactory extends Factory
{
    protected $model = EventLog::class;

    public function definition(): array
    {
        // TODO: delete this
        return [
            'event_type' => 'unknown.event',
            'event_level' => 0,
            'account_id' => null,
            'license_id' => null,
            'ip_address' => '127.0.0.1',
            'actor_id' => null,
            'details' => json_encode(['message' => 'Generic event']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function accountActivated($accountId, $licenseId, $ipAddress): static
    {
        return $this->state([
            'event_type' => 'account.activated',
            'event_level' => 0,
            'account_id' => $accountId,
            'license_id' => $licenseId,
            'ip_address' => $ipAddress,
            'actor_id' => $accountId,
            'details' => json_encode([
                'action' => 'license_activation',
                'result' => 'success',
                'timestamp' => now()->toISOString(),
            ]),
        ]);
    }

    public function deviceBound($accountId, $deviceId, $ipAddress): static
    {
        return $this->state([
            'event_type' => 'device.bound',
            'event_level' => 0,
            'account_id' => $accountId,
            'ip_address' => $ipAddress,
            'actor_id' => $accountId,
            'details' => json_encode([
                'action' => 'device_binding',
                'device_id' => $deviceId,
                'result' => 'success',
            ]),
        ]);
    }

    public function deviceUnbound($accountId, $deviceId, $ipAddress): static
    {
        return $this->state([
            'event_type' => 'device.unbound',
            'event_level' => 0,
            'account_id' => $accountId,
            'ip_address' => $ipAddress,
            'actor_id' => $accountId,
            'details' => json_encode([
                'action' => 'device_unbinding',
                'device_id' => $deviceId,
                'reason' => 'user_request',
            ]),
        ]);
    }

    public function loginAnomaly($accountId, $ipAddress, $details = []): static
    {
        return $this->state([
            'event_type' => 'login.anomaly',
            'event_level' => 1,
            'account_id' => $accountId,
            'ip_address' => $ipAddress,
            'actor_id' => $accountId,
            'details' => json_encode(array_merge([
                'action' => 'login_attempt',
                'result' => 'anomaly_detected',
                'reason' => 'unusual_location',
                'severity' => 'medium',
            ], $details)),
        ]);
    }

    public function accountSuspended($accountId, $adminId, $reason): static
    {
        return $this->state([
            'event_type' => 'account.suspended',
            'event_level' => 2, 
            'account_id' => $accountId,
            'ip_address' => '127.0.0.1', 
            'actor_id' => $adminId,
            'details' => json_encode([
                'action' => 'account_suspension',
                'reason' => $reason,
                'performed_by' => 'administrator',
            ]),
        ]);
    }

    public function licenseCreated($licenseId, $creatorId, $ipAddress): static
    {
        return $this->state([
            'event_type' => 'license.created',
            'event_level' => 0,
            'license_id' => $licenseId,
            'ip_address' => $ipAddress,
            'actor_id' => $creatorId,
            'details' => json_encode([
                'action' => 'license_creation',
                'source' => 'admin_panel',
            ]),
        ]);
    }
}