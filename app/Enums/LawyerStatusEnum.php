<?php

namespace App\Enums;

enum LawyerStatusEnum: int
{
    case NEW = 1;
    case PROCESS = 2;
    case ADMINISTRATIVE = 3;
    case DISASSEMBLY = 4;
}
