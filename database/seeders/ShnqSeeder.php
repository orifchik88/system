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

        NormativeDocument::query()->create([
            'id' => 7,
            'name' => 'ШНҚ 3.01.01-03'
        ]);

        NormativeDocument::query()->create([
            'id' => 8,
            'name' => 'ШНҚ 3.01.02-23 «Қурилишда хавфсизлик техникаси» шаҳарсозлик нормалари ва қоидалари'
        ]);

        NormativeDocument::query()->create([
            'id' => 9,
            'name' => 'ШНҚ 3.02.01 (ҚР 02.01)'
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

        Topic::query()->create([
            'id' => 61,
            'name' => '1.Умумий қоидалар',
            'parent_id' => 7,
        ]);

        Topic::query()->create([
            'id' => 62,
            'name' => '2. Қурилишда ишлаб чиқаришни тайёрлаш',
            'parent_id' => 7,
        ]);

        Topic::query()->create([
            'id' => 63,
            'name' => '3. Қурилишни ташкил этиш ва ишлаб чиқариш ишлари ҳужжатлари',
            'parent_id' => 7,
        ]);

        Topic::query()->create([
            'id' => 64,
            'name' => '4. Моддий-техник таъминот',
            'parent_id' => 7,
        ]);

        Topic::query()->create([
            'id' => 65,
            'name' => '5. Механизация ва транспорт',
            'parent_id' => 7,
        ]);

        Topic::query()->create([
            'id' => 66,
            'name' => '6.Меҳнатни ташкил қилиш',
            'parent_id' => 7,
        ]);

        Topic::query()->create([
            'id' => 67,
            'name' => '7. Қурилиш-монтаж ишлари сифатини таъминлаш',
            'parent_id' => 7,
        ]);

        Topic::query()->create([
            'id' => 68,
            'name' => '1. ҚЎЛЛАНИШ СОҲАСИ',
            'parent_id' => 7,
        ]);

        Topic::query()->create([
            'id' => 69,
            'name' => '8. Оператив - диспетчерлик бошқарув',
            'parent_id' => 7,
        ]);

        Topic::query()->create([
            'id' => 70,
            'name' => '9.Объектларни қайта таъмирлаш шароитида қурилишни ташкил этиш талаблари',
            'parent_id' => 7,
        ]);

        Topic::query()->create([
            'id' => 71,
            'name' => 'Умумий журнални олиб бориш бўйича кўрсатмалар',
            'parent_id' => 7,
        ]);

        Topic::query()->create([
            'id' => 72,
            'name' => 'Қурилишни ташкил этиш лойиҳасининг мазмун ва моҳияти',
            'parent_id' => 7,
        ]);

        Topic::query()->create([
            'id' => 73,
            'name' => '1-боб. Шаҳарсозлик нормалари ва қоидалари, санитария қоидалари, нормалари ва гигиена нормативлари ҳамда техник жиҳатдан тартибга солиш соҳасидаги норматив ҳужжатларга ҳаволалар',
            'parent_id' => 8,
        ]);

        Topic::query()->create([
            'id' => 74,
            'name' => '2-боб. Атамалар ва таърифлар',
            'parent_id' => 8,
        ]);

        Topic::query()->create([
            'id' => 75,
            'name' => '3-боб. Умумий қоидалар',
            'parent_id' => 8,
        ]);

        Topic::query()->create([
            'id' => 76,
            'name' => '4-боб. Қурилиш майдонлари ва иш жойларида хавфсизлик талаблари',
            'parent_id' => 8,
        ]);

        Topic::query()->create([
            'id' => 77,
            'name' => '5-боб. Қурилиш машиналарининг эксплуатацияси',
            'parent_id' => 8,
        ]);

        Topic::query()->create([
            'id' => 78,
            'name' => '6-боб. Технологик анжомлар ва асбоблар эксплуатацияси',
            'parent_id' => 8,
        ]);

        Topic::query()->create([
            'id' => 79,
            'name' => '7-боб. Транспортда ташиш ишлари',
            'parent_id' => 8,
        ]);
        Topic::query()->create([
            'id' => 80,
            'name' => '8-боб. Пайвандлаш ишлари',
            'parent_id' => 8,
        ]);

        Topic::query()->create([
            'id' => 81,
            'name' => '9-боб. Юклаш ва тушириш ишлари',
            'parent_id' => 8,
        ]);

        Topic::query()->create([
            'id' => 82,
            'name' => '10-боб. Изоляциялаш ишлари',
            'parent_id' => 8,
        ]);

        Topic::query()->create([
            'id' => 83,
            'name' => '11-боб. Сунъий асослар ва бурғулаш ишлари',
            'parent_id' => 8,
        ]);

        Topic::query()->create([
            'id' => 84,
            'name' => '12-боб. Ер ости ишлари',
            'parent_id' => 8,
        ]);

        Topic::query()->create([
            'id' => 85,
            'name' => '13-боб. Ер ишлари',
            'parent_id' => 8,
        ]);

        Topic::query()->create([
            'id' => 86,
            'name' => '14-боб. Бетон ва темир-бетон ишлари',
            'parent_id' => 8,
        ]);

        Topic::query()->create([
            'id' => 87,
            'name' => '15-боб. Тош-ғишт ишлари',
            'parent_id' => 8,
        ]);

        Topic::query()->create([
            'id' => 88,
            'name' => '16-боб. Қурилиш-монтаж ишлари',
            'parent_id' => 8,
        ]);

        Topic::query()->create([
            'id' => 89,
            'name' => '17-боб. Электр монтаж ишлари',
            'parent_id' => 8,
        ]);

        Topic::query()->create([
            'id' => 90,
            'name' => '18-боб. Ускуналарни синаш',
            'parent_id' => 8,
        ]);

        Topic::query()->create([
            'id' => 91,
            'name' => '19-боб. Том ишлари',
            'parent_id' => 8,
        ]);

        Topic::query()->create([
            'id' => 92,
            'name' => '20-боб. Пардозлаш ишлари',
            'parent_id' => 8,
        ]);

        Topic::query()->create([
            'id' => 93,
            'name' => '1-боб. Умумий қоидалар',
            'parent_id' => 9,
        ]);

        Topic::query()->create([
            'id' => 94,
            'name' => '2-боб. Атамалар ва таърифлар',
            'parent_id' => 9,
        ]);

        Topic::query()->create([
            'id' => 95,
            'name' => '3-боб. Ер иншоотлари. Замин ва пойдеворларни ўрнатишда хафсизлик талаблари',
            'parent_id' => 9,
        ]);

        Topic::query()->create([
            'id' => 96,
            'name' => '4-боб. Умумий талаблар',
            'parent_id' => 9,
        ]);

        Topic::query()->create([
            'id' => 97,
            'name' => '5-боб. Сув сатҳини пасайтириш, ер юзасидаги оқимни ташкил этиш, сув кетказиш ва дренаж',
            'parent_id' => 9,
        ]);

        Topic::query()->create([
            'id' => 98,
            'name' => '6-боб. Вертикал режалаштириш, чуқурларни қазиш',
            'parent_id' => 9,
        ]);

        Topic::query()->create([
            'id' => 99,
            'name' => '7-боб. Кўтармалар ва қайта тўлдиришлар',
            'parent_id' => 9,
        ]);

        Topic::query()->create([
            'id' => 100,
            'name' => '8-боб. Махсус грунт шароитидаги ер ишлари',
            'parent_id' => 9,
        ]);

        Topic::query()->create([
            'id' => 101,
            'name' => '9-боб. Грунтларда портлатиш ишлари',
            'parent_id' => 9,
        ]);

        Topic::query()->create([
            'id' => 102,
            'name' => '10-боб. Ер ишларини бажариш учун экологик талаблар',
            'parent_id' => 9,
        ]);

        Topic::query()->create([
            'id' => 103,
            'name' => '11-боб. Саёз жойлашган пойдеворлар',
            'parent_id' => 9,
        ]);

        Topic::query()->create([
            'id' => 104,
            'name' => '12-боб. Қозиқли пойдеворлар, шпунтли тўсиқлар, анкерлар',
            'parent_id' => 9,
        ]);


        Topic::query()->create([
            'id' => 105,
            'name' => '13-боб. Пастлашувчи қудуқлар ва кессонлар',
            'parent_id' => 9,
        ]);

        Topic::query()->create([
            'id' => 106,
            'name' => '14-боб. “Грунтдаги девор” усули билан қуриладиган иншоотлар',
            'parent_id' => 9,
        ]);

        Topic::query()->create([
            'id' => 107,
            'name' => '15-боб. Гидроизоляция ишлари',
            'parent_id' => 9,
        ]);

        Topic::query()->create([
            'id' => 108,
            'name' => '16-боб. Грунтларни мустаҳкамлаш',
            'parent_id' => 9,
        ]);

        Topic::query()->create([
            'id' => 109,
            'name' => '17-боб. Грунтни зичлаш, грунт ёстиқчаларини ўрнатиш ва сув билан тўйинган бўш грунтларни қурилишдан олдин зичлаш',
            'parent_id' => 9,
        ]);

        Topic::query()->create([
            'id' => 110,
            'name' => '18-боб. Грунтларни кучайтириш',
            'parent_id' => 9,
        ]);


        Topic::query()->create([
            'id' => 111,
            'name' => '20-боб. Грунтларни суньий музлатиш',
            'parent_id' => 9,
        ]);

        Topic::query()->create([
            'id' => 112,
            'name' => '1-ИЛОВА Сифатни назорат қилиш турлари. Атамалар ва таърифлар',
            'parent_id' => 9,
        ]);

        Topic::query()->create([
            'id' => 113,
            'name' => '2- ИЛОВА Ер ишларини бажаришдаги замин ва пойдеворлар ёпик ишларининг тахминий рўйхати.',
            'parent_id' => 9,
        ]);


        Topic::query()->create([
            'id' => 114,
            'name' => '4 – ИЛОВА Табиий жойлашган грунтларни вa грунт тўшамаларни тажрибавий зичлаш',
            'parent_id' => 9,
        ]);

        Topic::query()->create([
            'id' => 115,
            'name' => '9– ИЛОВА Ер иншоотлари, штабеллар ва уюмларни қуриш бўйича гидромеханизациялашган ишларнинг бажариш хусусиятлари бўйича кўрсатмалар',
            'parent_id' => 9,
        ]);

        Topic::query()->create([
            'id' => 116,
            'name' => '10 – ИЛОВА Заиф сувга тўйинган грунтларни қурилишдан олдин зичлашни ўзига хос хусусиятлари бўйича кўрсатмалар',
            'parent_id' => 9,
        ]);



    }
}
