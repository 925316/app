<?php

namespace App\Enums;

enum LicensePrivilege: int
{
    case DEFAULT = 0;
    case BASIC = 1;
    case REGULAR = 2;
    case ULTIMATE = 3;
    case TESTER = 4;
    case STAFF = 5;

    public function getLabel(): string
    {
        return match ($this) {
            self::DEFAULT => 'none',
            self::BASIC => 'basic',
            self::REGULAR => 'regular',
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
