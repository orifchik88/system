<?php

namespace App\Enums;

enum UserRoleEnum: int
{
   case RESKADR = 1;
   case REGKADR = 2;
   case INSPECTOR = 3;
   case REGISTRATOR = 4;
   case ICHKI = 5;
   case TEXNIK = 6;
   case MUALLIF = 7;
   case BUYURTMACHI = 8;
   case LOYIHA = 9;
   case QURILISH = 10;

//    public static function toArray(): array
//    {
//        return [
//            'inspector' => self::Inspector->value,
//            'author' => self::Author->value,
//            'designer' => self::Designer->value,
//            'technic' => self::Technic->value,
//        ];
//    }
//
//    public static function getValueByKey(string $key): ?string
//    {
//        $map = self::toArray();
//        return $map[$key] ?? null;
//    }

}
