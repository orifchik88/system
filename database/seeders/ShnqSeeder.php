<?php

namespace Database\Seeders;

use App\Models\NormativeDocument;
use App\Models\Topic;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShnqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        NormativeDocument::query()->create([
            'id' => 1,
            'name' => 'ШМҚ 3.01.03-19'
        ]);

        NormativeDocument::query()->create([
            'id' => 2,
            'name' => 'ШНҚ 2.01.02-04'
        ]);

        NormativeDocument::query()->create([
            'id' => 3,
            'name' => 'ШНҚ 2.03.10-2019 ТОМЛАР ВА ТОМҚОПЛАМАЛАР'
        ]);

        NormativeDocument::query()->create([
            'id' => 4,
            'name' => 'ШНҚ 2.04.07-22'
        ]);

        NormativeDocument::query()->create([
            'id' => 5,
            'name' => 'ШНҚ 2.09.10-22'
        ]);

        NormativeDocument::query()->create([
            'id' => 6,
            'name' => 'ShNQ 3.01.01-22 “Qurilishda ishlab chiqarishni tashkil qilish” shaharsozlik normalari va qoidalari'
        ]);



        Topic::query()->create([
            'id' => 1,
            'name' => '1. ҚЎЛЛАНИШ СОҲАСИ',
            'parent_id' => 1,
        ]);

        Topic::query()->create([
            'id' => 2,
            'name' => '2. НОРМАТИВ ҲАВОЛАЛАР',
            'parent_id' => 1,
        ]);
        Topic::query()->create([
            'id' => 3,
            'name' => '3. АТАМАЛАР ВА ТАЪРИФЛАР',
            'parent_id' => 1,
        ]);

        Topic::query()->create([
            'id' => 4,
            'name' => '4. УМУМИЙ МАЪЛУМОТЛАР',
            'parent_id' => 1,
        ]);

        Topic::query()->create([
            'id' => 5,
            'name' => '5. ГЕОДЕЗИК ИШЛАРНИ БАЖАРИШ ЛОЙИҲАСИ',
            'parent_id' => 1,
        ]);

        Topic::query()->create([
            'id' => 6,
            'name' => '6. ҚУРИЛИШ УЧУН ГЕОДЕЗИК РЕЖАЛАШ АСОСИ',
            'parent_id' => 1,
        ]);

        Topic::query()->create([
            'id' => 7,
            'name' => '7. ҚУРИЛИШ ЖАРАЁНИДАГИ РЕЖАЛАШ ИШЛАРИГА БЎЛГАН УМУМИЙ ТАЛАБЛАР',
            'parent_id' => 1,
        ]);

        Topic::query()->create([
            'id' => 8,
            'name' => '8. БИНОЛАРНИНГ ПОЙДЕВОРЛАРИ ВА ЕР ОСТИ ҚИСМЛАРИНИ ҚУРИШДА ГЕОДЕЗИК ИШЛАРНИ БАЖАРИШ',
            'parent_id' => 1,
        ]);

        Topic::query()->create([
            'id' => 9,
            'name' => '9. БИНО ВА ИНШОООТЛАРНИНГ ЕР УСТИ ҚИСМИНИ ҚУРИШДА ГЕОДЕЗИК ИШЛАРНИ БАЖАРИШ',
            'parent_id' => 1,
        ]);

        Topic::query()->create([
            'id' => 10,
            'name' => '10. БИНОЛАР ГЕОМЕТРИК ПАРАМЕТРЛАРИНИНГ АНИҚЛИГИНИ ГЕОДЕЗИК НАЗОРАТИ',
            'parent_id' => 1,
        ]);

        Topic::query()->create([
            'id' => 11,
            'name' => '11. МУҲАНДИСЛИК ТАРМОҚЛАРИ ВА ЕРОСТИ МУҲАНДИСЛИК КОММУНИКАЦИЯЛАРИ ТРАССАЛАРИНИ ЁТҚИЗИШДАГИ ГЕОДЕЗИК ИШЛАР',
            'parent_id' => 1,
        ]);

        Topic::query()->create([
            'id' => 12,
            'name' => '12. ГЕОДЕЗИК ИЖРОИЙ СЪЁМКАЛАР',
            'parent_id' => 1,
        ]);

        Topic::query()->create([
            'id' => 13,
            'name' => '13. БИНО ВА ИНШОООТЛАР ДЕФОРМАЦИЯЛАРИНИ ГЕОДЕЗИК КУЗАТИШЛАР',
            'parent_id' => 1,
        ]);

        Topic::query()->create([
            'id' => 14,
            'name' => '14. ҚУРИЛИШДА ГЕОДЕЗИК ИШЛАРИНИ БАЖАРИШДА МЕҲНАТ МУҲОФАЗАСИ',
            'parent_id' => 1,
        ]);

        Topic::query()->create([
            'id' => 15,
            'name' => '15. МУҲАНДИСЛИК ИЗЛАНИШЛАРИ МАЪЛУМОТЛАРИНИ ДАВЛАТ ШАҲАРСОЗЛИК КАДАСТРИ ГЕОАХБОРОТ ТИЗИМИГА(ДШК- ГАТ) ЭКСПОРТЛАШ',
            'parent_id' => 1,
        ]);

        Topic::query()->create([
            'id' => 16,
            'name' => '1. ҚЎЛЛАНИШ СОХАЛАРИ',
            'parent_id' => 2,
        ]);

        Topic::query()->create([
            'id' => 17,
            'name' => '2. МЕЪЁРИЙ ХОЛАТЛАР',
            'parent_id' => 1,
        ]);

        Topic::query()->create([
            'id' => 18,
            'name' => '3. УМУМИЙ ҲОЛАТЛАР',
            'parent_id' => 2,

        ]);

        Topic::query()->create([
            'id' => 19,
            'name' => '4. ЁНҒИН -ТЕХНИК ТАСНИФИ',
            'parent_id' => 2,
        ]);

        Topic::query()->create([
            'id' => 20,
            'name' => '5. ЁНҒИНГА ОДАМЛАР ХАВФСИЗЛИГИНИ ТАЪМИНЛАШ',
            'parent_id' => 2,
        ]);

        Topic::query()->create([
            'id' => 21,
            'name' => '6 ЁНҒИН ТАРҚАЛИШИНИНГ ОЛДИНИ ОЛИШ',
            'parent_id' => 2,
        ]);

        Topic::query()->create([
            'id' => 22,
            'name' => '7. ЎТ ЎЧИРИШ ВА ҚУТҚАРУВ ИШЛАРИ',
            'parent_id' => 2,
        ]);

        Topic::query()->create([
            'id' => 23,
            'name' => '1. УМУМИЙ ҲОЛАТЛАР',
            'parent_id' => 3,
        ]);

        Topic::query()->create([
            'id' => 24,
            'name' => '2. ТОМЛАР',
            'parent_id' => 3,
        ]);

        Topic::query()->create([
            'id' => 25,
            'name' => '3. ТОМҚОПЛАМАЛАР',
            'parent_id' => 3,
        ]);

        Topic::query()->create([
            'id' => 26,
            'name' => '4. УЗЕЛЛАР ВА ТУТАШУВЛАР',
            'parent_id' => 3,
        ]);

        Topic::query()->create([
            'id' => 27,
            'name' => '5. СУВ КЕТҚАЗУВЧИ (СУВ ҚОЧИРИШ) ҚУРИЛМАЛАР',
            'parent_id' => 3,
        ]);

        Topic::query()->create([
            'id' => 28,
            'name' => '6. ТОМҚОПЛАМАЛАРНИНГ ИШОНЧЛИГИНИ ТАЪМИНЛАШГА ОИД ЧОРА-ТАДБИРЛАР',
            'parent_id' => 3,
        ]);

        Topic::query()->create([
            'id' => 29,
            'name' => '7. ЁНҒИНГА ҚАРШИ ТАЛАБЛАР',
            'parent_id' => 3,
        ]);

        Topic::query()->create([
            'id' => 30,
            'name' => '8. ТОМЛАРНИНГ РЕКОНСТРУКЦИЯСИ',
            'parent_id' => 3,
        ]);

        Topic::query()->create([
            'id' => 31,
            'name' => '9. ТОМҚОПЛАМАЛАРНИ ТАЪМИРЛАШ',
            'parent_id' => 3,
        ]);

        Topic::query()->create([
            'id' => 32,
            'name' => '1-боб. Техник жиҳатдан тартибга солиш соҳасидаги норматив ҳужжатларга ҳаволалар',
            'parent_id' => 4,
        ]);

        Topic::query()->create([
            'id' => 33,
            'name' => '2-боб. Атамалар ва таърифлар',
            'parent_id' => 4,
        ]);

        Topic::query()->create([
            'id' => 34,
            'name' => '3-боб. Умумий қоидалар',
            'parent_id' => 4,
        ]);

        Topic::query()->create([
            'id' => 35,
            'name' => '4-боб. Иссиқлик миқдорлари',
            'parent_id' => 4,
        ]);

        Topic::query()->create([
            'id' => 36,
            'name' => '5-боб. Иссиқлик тармоқлари, иссиқлик билан таъминлаш тизимлари, конденсат тўплаш ва қайтариш тизимлари',
            'parent_id' => 4,
        ]);

        Topic::query()->create([
            'id' => 37,
            'name' => '6-боб. Иссиқлик ташувчилар ва уларнинг параметрлари. Иссиқликни узатишни тартибга солиш',
            'parent_id' => 4,
        ]);

        Topic::query()->create([
            'id' => 38,
            'name' => '7-боб. Иссиқлик билан таъминлаш тармоқларининг гидравлик ҳисоблари ва режимлари',
            'parent_id' => 4,
        ]);

        Topic::query()->create([
            'id' => 39,
            'name' => '8-боб. Иссиқлик тармоқлари трассаси ва уларни ўтказиш усуллари',
            'parent_id' => 4,
        ]);

        Topic::query()->create([
            'id' => 40,
            'name' => '9-боб. Қувур конструкциялари',
            'parent_id' => 4,
        ]);

        Topic::query()->create([
            'id' => 41,
            'name' => '10-боб. Қурилиш конструкциялари',
            'parent_id' => 4,
        ]);

        Topic::query()->create([
            'id' => 42,
            'name' => '11-боб. Қувурларни коррозиядан ҳимоялаш',
            'parent_id' => 4,
        ]);

        Topic::query()->create([
            'id' => 43,
            'name' => '12-боб. Иссиқлик пунктлари',
            'parent_id' => 4,
        ]);

        Topic::query()->create([
            'id' => 44,
            'name' => '13-боб. Электр таъминоти ва бошқарув тизими.',
            'parent_id' => 4,
        ]);

        Topic::query()->create([
            'id' => 45,
            'name' => '14-боб. Қурилишнинг махсус табиий шароитларида иссиқлик тармоқларини лойиҳалашга қўшимча талаблар.',
            'parent_id' => 4,
        ]);

        Topic::query()->create([
            'id' => 46,
            'name' => '1-боб. Техник жиҳатдан тартибга солиш соҳасидаги норматив ҳужжатларга ҳаволалар',
            'parent_id' => 5,
        ]);

        Topic::query()->create([
            'id' => 47,
            'name' => '2-боб. Умумий қоидалар',
            'parent_id' => 5,
        ]);

        Topic::query()->create([
            'id' => 48,
            'name' => '3-боб. Ҳажмли-режалаштириш ва конструктив ечимлар',
            'parent_id' => 5,
        ]);

        Topic::query()->create([
            'id' => 49,
            'name' => '4-боб. Сув таъминоти ва канализация',
            'parent_id' => 5,
        ]);

        Topic::query()->create([
            'id' => 50,
            'name' => '5-боб. Иситиш, шамоллатиш ва кондиционерлаш',
            'parent_id' => 5,
        ]);

        Topic::query()->create([
            'id' => 51,
            'name' => '6-боб. Электр таъминоти, электр ускуналари ва электр билан ёритиш',
            'parent_id' => 5,
        ]);

        Topic::query()->create([
            'id' => 52,
            'name' => '7-боб. Автоматлаштириш. Умумий талаблар',
            'parent_id' => 5,
        ]);

        Topic::query()->create([
            'id' => 53,
            'name' => '1-bob. Umumiy qoidalar',
            'parent_id' => 6,
        ]);

        Topic::query()->create([
            'id' => 54,
            'name' => '2-bob. Atamalar va ta’riflar',
            'parent_id' => 6,
        ]);

        Topic::query()->create([
            'id' => 55,
            'name' => '3-bob. Qurilishda ishlab chiqarish jarayonlariga tayyorgarlik ko‘rish',
            'parent_id' => 6,
        ]);

        Topic::query()->create([
            'id' => 56,
            'name' => '4-bob. Qurilishni tashkil etish va ishlab chiqarish ishlari',
            'parent_id' => 6,
        ]);

        Topic::query()->create([
            'id' => 57,
            'name' => '5-bob. Moddiy-texnik ta’minot',
            'parent_id' => 6,
        ]);

        Topic::query()->create([
            'id' => 58,
            'name' => '6-bob. Mexanizatsiya va transport ishlari',
            'parent_id' => 6,
        ]);

        Topic::query()->create([
            'id' => 59,
            'name' => '7-bob. Qurilish-montaj ishlarining sifatini ta’minlash ',
            'parent_id' => 6,
        ]);

        Topic::query()->create([
            'id' => 60,
            'name' => '8-bob. Qurilishda ishlab chiqarishini tashkil etishda atrof-muhit muhofazasiga qo‘yilgan talablar',
            'parent_id' => 6,
        ]);





    }
}
