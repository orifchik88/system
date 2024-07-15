<?php

namespace App\Enums;

enum UserRoleEnum: string
{
    case Inspector = 'Inspector';
    case Author = 'Texnik Nazoratchi';
    case Designer = 'Loyixachi';
    case Technic= 'Ichki nazoratchi';

    public static function toArray(): array
    {
        return [
            'inspector' => self::Inspector->value,
            'author' => self::Author->value,
            'designer' => self::Designer->value,
            'technic' => self::Technic->value,
        ];
    }

    public static function getValueByKey(string $key): ?string
    {
        $map = self::toArray();
        return $map[$key] ?? null;
    }
}
