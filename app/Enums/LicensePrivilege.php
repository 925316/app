<?php

namespace App\Enums;

enum LicensePrivilege: int
{
    case DEFAULT = 0;
    case STANDARD = 1;
    case UPGRADE = 2;
    case ULTIMATE = 3;
    case TESTER = 6;
    case STAFF = 7;

    public function getLabel(): string
    {
        return match ($this) {
            self::DEFAULT => 'none',
            self::STANDARD => 'standard',
            self::UPGRADE => 'upgrade',
            self::ULTIMATE => 'ultimate',
            self::TESTER => 'tester',
            self::STAFF => 'staff',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])
            ->toArray();
    }
}
