<?php

namespace App\Enums;

enum RegulationStatusEnum: int
{
    case PROVIDE_REMEDY = 1;
    case CONFIRM_REMEDY = 2;
    case ATTACH_DEED = 3;
    case CONFIRM_DEED = 4;
    case CONFIRM_DEED_CMR = 5;
    case ELIMINATED = 6;
    case IN_LAWYER = 7;
    case LATE_EXECUTION = 8;
}
