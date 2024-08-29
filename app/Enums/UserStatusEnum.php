<?php

namespace App\Enums;

enum UserStatusEnum: int
{
    case ACTIVE = 1;
    case ON_HOLIDAY = 2;
    case RELEASED = 3;

}
