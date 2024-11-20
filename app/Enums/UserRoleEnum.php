<?php

namespace App\Enums;

enum UserRoleEnum: int
{

    case RESKADR = 1;
    case REGKADR = 2;
    case INSPECTOR = 3;
    case REGISTRATOR = 4;
    case ICHKI = 5;
    case TEXNIK = 6;
    case MUALLIF = 7;
    case BUYURTMACHI = 8;
    case LOYIHA = 9;
    case QURILISH = 10;
    case OPERATOR = 11;
    case INSPEKSIYA = 12;
    case HUDUDIY_KUZATUVCHI = 13;
    case RESPUBLIKA_KUZATUVCHI = 14;
    case FVB = 15;
    case SEOM = 16;
    case NOGIRONLAR_JAM = 17;
    case NOGIRONLAR_ASSOT = 18;
    case UY_JOY_INSPEKSIYA = 19;
    case QURILISH_MONTAJ = 26;
    case SEOM_RES_KADR = 20;
    case SEOM_REG_KADR = 21;
    case FVV_RES_KADR = 22;
    case FVB_REG_KADR = 23;
    case NOGIRONLAR_JAM_KADR = 24;
    case NOGIRONLAR_ASSOT_KADR = 25;
    case YURIST = 27;
    case BUXGALTER = 28;

    case ADMIN = 29;
}
