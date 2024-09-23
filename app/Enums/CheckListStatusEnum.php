<?php

namespace App\Enums;

enum CheckListStatusEnum: int
{
    case CONFIRMED = 1; // Tasdiqlangan
    case IMPERFECT = 2; //Qisman tasdiqlangan
    case NOT_FILLED = 3; //To'ldirilmagan
    case RAISED = 4; // ETIROZ
    case FILLED = 5; // TOLDIRILGAN
}
