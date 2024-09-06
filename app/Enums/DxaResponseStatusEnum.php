<?php

namespace App\Enums;

enum DxaResponseStatusEnum: int
{
    case NEW = 1;
    case SEND_INSPECTOR = 2;
    case IN_REGISTER = 3;
    case ACCEPTED = 4;
    case REJECTED = 5;
    case CANCELED = 6;
}
