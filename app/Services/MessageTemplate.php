<?php

namespace App\Services;

class MessageTemplate
{
    public static function objectCreated($surname, $objectNumber, $roleName): string
    {
        $domain = config('app.url');
        return 'Hurmatli ' . $surname . ', Qurilish nazorati tizimida yangi obyekt ro\'yxatdan o\'tdi, obyektda siz ' .
            $roleName . 'siz! Obyekt raqami - ' . $objectNumber . ' ,' . $domain .
            ' "Shaffof qurilish" MAT.';
    }

    public static function regulationCreated($regulationNumber, $objectNumber)
    {
        return 'Sizga yangi yozma ko\'rsatma berildi! Yozma ko\'rsatma raqami - '.$regulationNumber.', obyekt raqami - '.$objectNumber
                .'Shaffof qurilish MAT.';
    }

    public static function checklistCreated($objectNumber): string
    {
        return $objectNumber.' obyektda ayrim ishlar yakunlandi, tasdiqlash yoki e\'tirozlarni kiritish muddati 3 kun.
                Shaffof qurilish MAT.';
    }

    public static function rejectRegulation($objectNumber, $regulationNumber)
    {
        return 'Yozma ko\'rsatma rad etildi. Yozma ko\'rsatma raqami - '.$regulationNumber.', obyekt raqami - '.$objectNumber.
                'Shaffof qurilish MAT.';
    }

    public static function acceptRegulation($objectNumber, $regulationNumber)
    {
        return 'Yozma ko\'rsatma ijrosi tasdiqlandi. Yozma ko\'rsatma raqami - '.$regulationNumber.', obyekt raqami - '.$objectNumber.
            'Shaffof qurilish MAT.';
    }
}
