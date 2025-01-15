<?php

namespace App\Enums;

enum ConstructionWork: int
{
    case NEW = 1;
    case RECONSTRUCTION = 2;
    case OTHER = 3;

    public static function fromString(string $difficulty): ?self
    {
        return match ($difficulty) {
            'Yangi qurilish' => self::NEW,
            'Rekonstruksiya' => self::RECONSTRUCTION,
            default => self::OTHER,
        };
    }
}
