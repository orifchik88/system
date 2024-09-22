<?php

namespace App\Enums;

enum LevelStatusEnum: int
{
    case  NOT_BEGIN = 1;
    case  IN_PROCESS = 2;
    case  FINISHED = 3;
}
