<?php

namespace App\Enums;

enum ObjectCheckEnum: string
{
    case INSPEKTOR = 'inspector';

    case TEXNIK_NAZORAT = 'texnik';

    case MUALLIF = 'muallif';
    case ICHKI_NAZORAT = 'ichki';
//    case BUYURTMACHI = 'buyurtmachi';
//    case LOYIHA_TASHKILOTI = 'loyiha';
//
//    case QURILISH_TASHKILOTI = 'qurilish';
}
