<?php

namespace App\Enums;

enum DxaResponseStatusEnum: int
{
    case CHECKED = 1;
    case ARCHIVE = 2;
    case NEW = 3;
    case RE_REGISTRATION = 4;
    case SEND_INSPECTOR = 5;
}
