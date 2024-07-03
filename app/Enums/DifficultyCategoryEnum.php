<?php

namespace App\Enums;

enum DifficultyCategoryEnum: int
{
    case I = 1;
    case II = 2;
    case III = 3;
    case IV = 4;

    public static function getLabel(self $difficulty): ?int
    {
        return match ($difficulty) {
            self::I => 1,
            self::II => 2,
            self::III => 3,
            self::IV => 4,
        };
    }

    public static function fromString(string $difficulty): ?self
    {
        return match ($difficulty) {
            'I' => self::I,
            'II' => self::II,
            'III' => self::III,
            'IV' => self::IV,
            default => null,
        };
    }
}
