<?php

namespace App\Enums;

enum LicenseType: int
{
    case BASE = 1;
    case UPGRADE = 2;

    public function getLabel(): string
    {
        return match ($this) {
            self::BASE => 'base',
            self::UPGRADE => 'upgrade',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])
            ->toArray();
    }
}
