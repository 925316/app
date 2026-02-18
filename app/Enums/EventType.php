<?php

namespace App\Enums;

enum EventType: string
{
    // Account events
    case ACCOUNT_REGISTERED = 'account.registered';
    case ACCOUNT_LOGIN = 'account.login';
    case ACCOUNT_LOGOUT = 'account.logout';
    case ACCOUNT_PROFILE_UPDATED = 'account.profile_updated';
    case ACCOUNT_CREATED = 'account.created';
    case ACCOUNT_UPDATED = 'account.updated';
    case ACCOUNT_DELETED = 'account.deleted';
    case ACCOUNT_SUSPENDED = 'account.suspended';
    case ACCOUNT_UNSUSPENDED = 'account.unsuspended';
    case ACCOUNT_HWID_RESET = 'account.hwid_reset';
    case ACCOUNT_EMAIL_VERIFIED = 'account.email_verified';

    // License events
    case LICENSE_CREATED = 'license.created';
    case LICENSE_ACTIVATED = 'license.activated';
    case LICENSE_SUSPENDED = 'license.suspended';
    case LICENSE_REACTIVATED = 'license.reactivated';
    case LICENSE_REVOKED = 'license.revoked';
    case LICENSE_UPGRADED = 'license.upgraded';
    case LICENSE_EXTENDED = 'license.extended';
    case LICENSE_EXPIRED = 'license.expired';

    // Device events
    case DEVICE_BOUND = 'device.bound';
    case DEVICE_UNBOUND = 'device.unbound';
    case DEVICE_HWID_CHANGED = 'device.hwid_changed';
    case DEVICE_ADMIN_UNBOUND = 'device.admin_unbound';
    case DEVICE_ADMIN_HWID_RESET = 'device.admin_hwid_reset';

    // System events
    case SYSTEM_PACKAGE_UPLOADED = 'system.package_uploaded';
    case SYSTEM_STATISTICS_UPDATED = 'system.statistics_updated';

    /**
     * Get all event types for a specific category
     */
    public static function getCategoryEvents(string $category): array
    {
        return array_filter(
            self::cases(),
            fn ($case) => str_starts_with($case->value, $category.'.')
        );
    }

    /**
     * Get the category part of the event type
     */
    public function category(): string
    {
        return explode('.', $this->value)[0];
    }

    /**
     * Get the action part of the event type
     */
    public function action(): string
    {
        return explode('.', $this->value)[1];
    }

    /**
     * Get a human-readable label for the event type
     */
    public function label(): string
    {
        return match ($this) {
            self::ACCOUNT_REGISTERED => 'Account Registered',
            self::ACCOUNT_LOGIN => 'Login',
            self::ACCOUNT_LOGOUT => 'Logout',
            self::ACCOUNT_PROFILE_UPDATED => 'Profile Updated',
            self::ACCOUNT_CREATED => 'Account Created',
            self::ACCOUNT_UPDATED => 'Account Updated',
            self::ACCOUNT_DELETED => 'Account Deleted',
            self::ACCOUNT_SUSPENDED => 'Account Suspended',
            self::ACCOUNT_UNSUSPENDED => 'Account Unsuspended',
            self::ACCOUNT_HWID_RESET => 'HWID Reset',
            self::ACCOUNT_EMAIL_VERIFIED => 'Email Verified',

            self::LICENSE_CREATED => 'License Created',
            self::LICENSE_ACTIVATED => 'License Activated',
            self::LICENSE_SUSPENDED => 'License Suspended',
            self::LICENSE_REACTIVATED => 'License Reactivated',
            self::LICENSE_REVOKED => 'License Revoked',
            self::LICENSE_UPGRADED => 'License Upgraded',
            self::LICENSE_EXTENDED => 'License Extended',
            self::LICENSE_EXPIRED => 'License Expired',

            self::DEVICE_BOUND => 'Device Bound',
            self::DEVICE_UNBOUND => 'Device Unbound',
            self::DEVICE_HWID_CHANGED => 'HWID Changed',
            self::DEVICE_ADMIN_UNBOUND => 'Device Unbound (Admin)',
            self::DEVICE_ADMIN_HWID_RESET => 'HWID Reset (Admin)',

            self::SYSTEM_PACKAGE_UPLOADED => 'Package Uploaded',
            self::SYSTEM_STATISTICS_UPDATED => 'Statistics Updated',
        };
    }

    /**
     * Get all event types grouped by category
     */
    public static function groupedByCategory(): array
    {
        $grouped = [];

        foreach (self::cases() as $case) {
            $category = $case->category();
            if (! isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = $case;
        }

        return $grouped;
    }

    /**
     * Check if this event type belongs to a specific category
     */
    public function isCategory(string $category): bool
    {
        return $this->category() === $category;
    }

    /**
     * Check if this event type has a specific action
     */
    public function isAction(string $action): bool
    {
        return $this->action() === $action;
    }
}
