<?php

namespace App\Enums;

enum ObjectCheckEnum: string
{
    case INSPECTOR = 'isInspector';
    case TECHNIC = 'isTechnic';
    case DESIGNER = 'isDesigner';
    case AUTHOR = 'isAuthor';
}
