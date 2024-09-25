<?php

namespace App\Enums;

enum WorkTypeStatusEnum: int
{
    case NOT_STARTED = 1;
    case CONFIRMED = 2;
    case IN_PROCESS = 3;
}
