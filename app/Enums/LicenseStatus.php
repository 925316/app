<?php

namespace App\Enums;

enum LicenseStatus: int
{
    case UNUSED = 0;
    case ACTIVE = 1;
    case SUSPENDED = 2;
    case EXPIRED = 3;
    case UPGRADED = 4;
    case REVOKED = 5;

    /**
     * Obtain status label
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::UNUSED => 'unused',
            self::ACTIVE => 'active',
            self::SUSPENDED => 'suspended',
            self::EXPIRED => 'expired',
            self::UPGRADED => 'upgraded',
            self::REVOKED => 'revoked',
        };
    }

    /**
     * Obtain the corresponding color for the status
     */
    public function getColor(): string
    {
        return match ($this) {
            self::UNUSED => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
            self::ACTIVE => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            self::SUSPENDED => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            self::EXPIRED => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            self::UPGRADED => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
            self::REVOKED => 'bg-gray-800 text-white dark:bg-gray-900 dark:text-gray-100',
        };
    }

    /**
     * Check if it is in an active state
     */
    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    /**
     * Check if it can be activated
     * Only unused licenses can be activated
     */
    public function canActivate(): bool
    {
        return $this === self::UNUSED;
    }

    /**
     * Check if it can be restored to an active state
     * Suspended licenses can be restored
     */
    public function canReactivate(): bool
    {
        return $this === self::SUSPENDED;
    }

    /**
     * Check if an upgrade is possible
     * The activated license can be upgraded
     */
    public function canUpgrade(): bool
    {
        return $this === self::ACTIVE;
    }

    /**
     * Check if it is possible to suspend
     * The activated license can be suspended
     */
    public function canSuspend(): bool
    {
        return $this === self::ACTIVE;
    }

    /**
     * Check if it is possible to undo
     * Any non-undone state can be undone
     */
    public function canRevoke(): bool
    {
        return $this !== self::REVOKED;
    }

    /**
     * get all status options
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])
            ->toArray();
    }

    /**
     * get all status options with color
     */
    public static function colorOptions(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->getColor()])
            ->toArray();
    }

    /**
     * check if it is invalid
     */
    public function isInvalid(): bool
    {
        return in_array($this, [self::EXPIRED, self::REVOKED]);
    }

    /**
     * check if it is valid
     */
    public function isValid(): bool
    {
        return in_array($this, [self::ACTIVE, self::SUSPENDED]);
    }
}
