<?php

namespace App\Enums;

enum CheckListStatusEnum: int
{
    case NOT_FILLED = 1; // Toldirilmagan
    case FIRST = 2; //  1-Tasdiqlashdan
    case  SECOND = 3; // 2-Tasdiqlashdan
    case RAISED = 4; // ETIROZ
    case CONFIRMED = 5; //Tasdiqlangan
    case AUTO_CONFIRMED = 6; // Avtomatik tasdiqlangan
}
