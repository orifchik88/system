<?php

namespace App\Enums;

use function Laravel\Prompts\select;

enum ConstructionWork: int
{
    case NEW = 1; // yangi
    case RECONSTRUCTION = 2;// Rekonstruksiya
    case MAJOR_RECONSTRUCTION = 4; //Mukammal ta'mirlash
    case CURRENT_RECONSTRUCTION = 5; //Joriy tamirlash
    case OTHER = 3;

    public static function fromString(?string $difficulty = null): ?self
    {
        return match ($difficulty) {
            'Yangi qurilish' => self::NEW,
            'Rekonstruksiya' => self::RECONSTRUCTION,
            'Mukammal ta\'mirlash' => self::MAJOR_RECONSTRUCTION,
            'Joriy tamirlash' => self::CURRENT_RECONSTRUCTION,
            default => self::OTHER,
        };
    }
}
