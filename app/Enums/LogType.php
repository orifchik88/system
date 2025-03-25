<?php


namespace App\Enums;

class LogType
{
    const TASK_HISTORY = 1;
    const CLAIM_HISTORY = 2;

    const ARTICLE_HISTORY = 3;
    const ARTICLE_CREATE_HISTORY = 4;
    const ARTICLE_PRICE_HISTORY = 5;
    const ARTICLE_PRICE_DELETE = 6;
    const ARTICLE_INSPECTOR_HISTORY = 7;
    const ARTICLE_ROTATION = 8;
    const ARTICLE_UPDATE_HISTORY = 9;
    const ARTICLE_MONITORING = 10;

    const ARTICLE_PAYMENT_CREATE = 11;
    const ARTICLE_DEADLINE_CHANGE = 12;

    public static function getLabel($type): string
    {
        switch ($type) {
            case self::ARTICLE_HISTORY:
                return 'Status o\'zgartirildi';
            case self::ARTICLE_CREATE_HISTORY:
                return 'Obyekt yaratildi';
            case self::ARTICLE_PRICE_HISTORY:
                return 'Obyekt to\'lov miqdori o\'zgartirildi';
            case self::ARTICLE_PRICE_DELETE:
                return 'Obyektda to\'lov miqdori o\'chirildi';
            case self::ARTICLE_ROTATION:
                return 'Obyektda inspektor rotatsiya qilindi';
            case self::ARTICLE_UPDATE_HISTORY:
                return 'Obyekt qayta rasmiylashtirildi';
            case self::ARTICLE_INSPECTOR_HISTORY:
                return 'Obyektda inspektor o\'zgardi';
            case self::ARTICLE_MONITORING:
                return 'Obyektda monitoring o\'tkazildi';
            case self::ARTICLE_DEADLINE_CHANGE:
                return 'Obyekt muddati o\'zgartirildi';
            default:
                return 'Noma’lum tur';
        }
    }
}
