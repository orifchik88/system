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



    public static function attachRegulationInspector($username, $objectNumber, $blockName, $roleName, $date)
    {
        return "$objectNumber raqamli obyekt, $blockName blokida ayrim ishlar yakunlandi, 3 kun ichida tanishib chiqishni unutmang!!! <br> <br> $username ($roleName) <br> $date";
    }

    public static function confirmRegulationInspector($username, $objectNumber, $regulationNumber, $blockName, $roleName, $date)
    {
        return "$objectNumber raqamli obyekt, $blockName blokida $regulationNumber raqamli yozma ko'rsatma tasdiqlash uchun keldi!<br> <br> $username ($roleName) <br> $date";
    }

    public static function changeUserInObject($username,$changedRole, $objectNumber, $roleName, $date)
    {
        return "$objectNumber raqamli obyekt, $changedRole xodimi o'zgartirilmoqda. Tanishib chiqishni unutmang!!! <br> <br> $username ($roleName) <br> $date";
    }
    public static function askDate($username, $objectNumber, $regulationNumber,  $roleName, $date)
    {
        return "$objectNumber raqamli obyekt, $regulationNumber raqamli yozma ko'rsatmaga muddat uzaytirish so'raldi <br> <br> $username ($roleName) <br> $date";
    }
    public static function attachObjectInspector($username, $objectNumber, $roleName, $date)
    {
        return  "$objectNumber raqamli obyektni ro'yxatdan o'tkazish uchun ariza keldi! <br> <br> $username ($roleName) <br> $date";
    }

    public static function createdObject($username, $objectNumber, $roleName, $date)
    {
        return  "$objectNumber raqamli yangi obyekt sizga biriktirildi! <br> <br> $username ($roleName) <br> $date";
    }

    public static function createdClaim($username, $objectNumber, $roleName, $date)
    {
        return  "$objectNumber raqamli obyektni foydalanishga topshirish uchun ariza keldi! <br> <br> $username ($roleName) <br> $date";
    }

    public static function ratationInspector($inspectorName, $username, $roleName, $date)
    {
        return  "$inspectorName bilan o'zgartirildingiz! <br> <br> $username ($roleName) <br> $date";
    }


    public static function regulationCreated($regulationNumber, $objectNumber)
    {
        return 'Sizga yangi yozma ko\'rsatma berildi! Yozma ko\'rsatma raqami - '.$regulationNumber.', obyekt raqami - '.$objectNumber
                .' Shaffof qurilish MAT.';
    }

    public static function checklistCreated($objectNumber): string
    {
        return $objectNumber.' obyektda ayrim ishlar yakunlandi, tasdiqlash yoki e\'tirozlarni kiritish muddati 3 kun. Shaffof qurilish MAT.';
    }

    public static function rejectRegulation($objectNumber, $regulationNumber)
    {
        return 'Yozma ko\'rsatma rad etildi. Yozma ko\'rsatma raqami - '.$regulationNumber.', obyekt raqami - '.$objectNumber.
                ' Shaffof qurilish MAT.';
    }

    public static function acceptRegulation($objectNumber, $regulationNumber)
    {
        return 'Yozma ko\'rsatma ijrosi tasdiqlandi. Yozma ko\'rsatma raqami - '.$regulationNumber.', obyekt raqami - '.$objectNumber.
            ' Shaffof qurilish MAT.';
    }
}
