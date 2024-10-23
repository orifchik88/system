<?php

namespace App\Enums;

enum UserHistoryStatusEnum: int
{
    case ASKED = 1;
    case ACCEPTED = 2;
    case REJECTED = 3;
}
