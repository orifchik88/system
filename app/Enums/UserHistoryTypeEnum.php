<?php

namespace App\Enums;

enum UserHistoryTypeEnum: int
{
    case CHANGE = 1;
    case ROTATION = 2;
    case CREATE = 3;
    case EDIT = 4;
    case DELETE = 5;

}
