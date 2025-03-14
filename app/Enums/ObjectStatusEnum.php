<?php

namespace App\Enums;

enum ObjectStatusEnum: int
{
    case NEW = 1;
    case PROGRESS = 2;
    case FROZEN = 3;
    case SUSPENDED = 4;
    case SUBMITTED = 5;
    case CANCELED = 6;
}
