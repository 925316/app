<?php

namespace App\Services;

use App\Enums\LicensePrivilege;
use App\Enums\LicenseStatus;
use App\Models\Account;
use App\Models\License;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LicenseService
{
    public const LICENSE_KEY_PATTERN = '^[A-Z0-9]{5}-[0-9A-F]{5}-[A-Z2-7]{5}-[A-Z3-8]{5}-[A-Z0-9]{5}$';

    private const SEGMENT_ALPHABETS = [
        'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
        '0123456789ABCDEF',
        'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567',
        'ABCDEFGHIJKLMNOPQRSTUVWXYZ345678',
        'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
    ];

    /**
     * Generate a new license key
     * Format: XXXXX-XXXXX-XXXXX-XXXXX-XXXXX (25 uppercase alphanumeric chars, 5 groups)
     */
    public static function generateLicenseKey(): string
    {
        $groups = [];

        foreach (self::SEGMENT_ALPHABETS as $alphabet) {
            $groups[] = self::generateSegment($alphabet, 5);
        }

        return implode('-', $groups);
    }

    /**
     * Validate license key format
     */
    public static function validateLicenseKeyFormat(string $key): bool
    {
        return preg_match('/'.self::LICENSE_KEY_PATTERN.'/', strtoupper($key)) === 1;
    }

    private static function generateSegment(string $alphabet, int $length): string
    {
        $segment = '';
        $maxIndex = strlen($alphabet) - 1;

        for ($i = 0; $i < $length; $i++) {
            $segment .= $alphabet[random_int(0, $maxIndex)];
        }

        return $segment;
    }

    /**
     * Create a new license
     */
    public static function createLicense(
        int $privilege = LicensePrivilege::DEFAULT->value,
        ?int $accountId = null,
        ?string $key = null,
        ?string $expiresAt = null,
        ?string $notes = null
    ): License {
        return License::create([
            'key' => $key ?? self::generateLicenseKey(),
            'privilege' => $privilege,
            'status' => LicenseStatus::UNUSED->value,
            'used_by' => $accountId,
            'expires_at' => $expiresAt ?? now()->addYear(),
            'notes' => $notes,
        ]);
    }

    /**
     * Activate a license for an account
     * Note: Privilege level checks should be done by the controller, not here
     */
    public static function activateLicense(License $license, Account $account, ?string $ipAddress = null): bool
    {
        if ($license->isExpired()) {
            throw ValidationException::withMessages([
                'license' => 'License has expired.',
            ]);
        }

        if (! $license->canActivate()) {
            throw ValidationException::withMessages([
                'license' => 'License cannot be activated. Current status: '.$license->status->getLabel(),
            ]);
        }

        // Check if the license can be activated based on privilege level
        if (! $license->canActivateByPrivilege()) {
            throw ValidationException::withMessages([
                'license' => 'License upgrade cannot be activated alone. It must be used to upgrade a standard license.',
            ]);
        }

        // Note: We no longer check for existing active licenses here
        // The controller should handle privilege level upgrade/downgrade logic

        return DB::transaction(function () use ($license, $account, $ipAddress) {
            // Re-fetch with a pessimistic lock to prevent concurrent activations
            $locked = License::lockForUpdate()->find($license->id);

            if (! $locked || $locked->isExpired() || ! $locked->canActivate()) {
                throw ValidationException::withMessages([
                    'license' => 'License cannot be activated. It may have already been activated by another request.',
                ]);
            }

            $result = $locked->activate($account->id, $ipAddress);

            // Sync the caller's instance so it reflects the saved state
            $license->setRawAttributes($locked->getAttributes());
            $license->syncOriginal();

            return $result;
        });
    }

    /**
     * Suspend a license
     */
    public static function suspendLicense(License $license, ?string $notes = null): bool
    {
        if (! $license->status->canSuspend()) {
            throw ValidationException::withMessages([
                'license' => 'License cannot be suspended. Current status: '.$license->status->getLabel(),
            ]);
        }

        $license->status = LicenseStatus::SUSPENDED;
        $license->suspended_at = now();

        if ($notes) {
            $license->notes = $notes;
        }

        return $license->save();
    }

    /**
     * Reactivate a suspended license
     */
    public static function reactivateLicense(License $license): bool
    {
        if (! $license->status->canReactivate()) {
            throw ValidationException::withMessages([
                'license' => 'License cannot be reactivated. Current status: '.$license->status->getLabel(),
            ]);
        }

        $license->status = LicenseStatus::ACTIVE;
        $license->suspended_at = null;

        return $license->save();
    }

    /**
     * Revoke a license
     */
    public static function revokeLicense(License $license, ?string $notes = null): bool
    {
        if (! $license->status->canRevoke()) {
            throw ValidationException::withMessages([
                'license' => 'License cannot be revoked. Current status: '.$license->status->getLabel(),
            ]);
        }

        $license->status = LicenseStatus::REVOKED;

        if ($notes) {
            $license->notes = $notes;
        }

        return $license->save();
    }

    /**
     * Upgrade a license
     */
    public static function upgradeLicense(License $license, int $newPrivilege, ?string $notes = null): bool
    {
        return DB::transaction(function () use ($license, $newPrivilege, $notes) {
            $locked = License::lockForUpdate()->find($license->id);

            if (! $locked || ! $locked->status->canUpgrade()) {
                throw ValidationException::withMessages([
                    'license' => 'License cannot be upgraded. Current status: '.($locked?->status->getLabel() ?? 'unknown'),
                ]);
            }

            $locked->status = LicenseStatus::UPGRADED;
            $locked->privilege = $newPrivilege;

            if ($notes) {
                $locked->notes = $notes;
            }

            $saved = $locked->save();

            $license->setRawAttributes($locked->getAttributes());
            $license->syncOriginal();

            return $saved;
        });
    }

    /**
     * Check if a license key is valid and can be used
     */
    public static function isLicenseValid(string $key): bool
    {
        $license = License::where('key', $key)->first();

        if (! $license) {
            return false;
        }

        return $license->canActivate();
    }

    /**
     * Get license by key
     */
    public static function getLicenseByKey(string $key): ?License
    {
        return License::where('key', $key)->first();
    }

    /**
     * Get all licenses for an account
     */
    public static function getLicensesForAccount(int $accountId): \Illuminate\Database\Eloquent\Collection
    {
        return License::where('used_by', $accountId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get active license for an account
     */
    public static function getActiveLicenseForAccount(int $accountId): ?License
    {
        return License::getActiveLicense($accountId);
    }

    /**
     * Check if account can activate a new license
     */
    public static function canAccountActivateLicense(int $accountId): bool
    {
        return ! License::hasActiveLicense($accountId);
    }

    /**
     * Extend license expiration
     */
    public static function extendLicenseExpiration(License $license, int $days): bool
    {
        if ($license->isExpired() || $license->isRevoked()) {
            throw ValidationException::withMessages([
                'license' => 'Cannot extend expiration for this license status.',
            ]);
        }

        $license->expires_at = $license->expires_at->addDays($days);

        return $license->save();
    }

    /**
     * Get license status history
     */
    public static function getLicenseStatusHistory(License $license): array
    {
        // This would be more sophisticated with a status history table
        // For now, return basic info
        return [
            'current_status' => $license->status->getLabel(),
            'activated_at' => $license->activated_at,
            'suspended_at' => $license->suspended_at,
            'expires_at' => $license->expires_at,
            'days_until_expiry' => $license->daysUntilExpiry(),
        ];
    }
}
