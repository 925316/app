<?php

namespace App\Services;

use App\Enums\LicensePrivilege;
use App\Enums\LicenseStatus;
use App\Models\Account;
use App\Models\License;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LicenseService
{
    /**
     * Generate a new license key
     * Format: XXXXX-XXXXX-XXXXX-XXXXX-XXXXX (25 uppercase alphanumeric chars, 5 groups)
     */
    public static function generateLicenseKey(): string
    {
        $groups = [];
        for ($i = 0; $i < 5; $i++) {
            $groups[] = strtoupper(Str::random(5));
        }

        return implode('-', $groups);
    }

    /**
     * Validate license key format
     */
    public static function validateLicenseKeyFormat(string $key): bool
    {
        return preg_match('/^[A-Z0-9]{5}-[A-Z0-9]{5}-[A-Z0-9]{5}-[A-Z0-9]{5}-[A-Z0-9]{5}$/', $key) === 1;
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

        return $license->activate($account->id, $ipAddress);
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
        if (! $license->status->canUpgrade()) {
            throw ValidationException::withMessages([
                'license' => 'License cannot be upgraded. Current status: '.$license->status->getLabel(),
            ]);
        }

        $license->status = LicenseStatus::UPGRADED;
        $license->privilege = $newPrivilege;

        if ($notes) {
            $license->notes = $notes;
        }

        return $license->save();
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
