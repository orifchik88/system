<?php

namespace App\Enums;

enum UserStatusEnum: int
{
    case ADDITIONAL_LEAVE = 1;
    case SICK_LEAVE = 2;
    case JOURNEY_LEAVE = 3;
    case STUDY_LEAVE = 4;
    case WITHOUT_REASON = 5;
    case ACTIVE = 6;
    case NOT_ACTIVE = 7;
    case RELEASED = 8;

}
