<?php

namespace Database\Seeders;

use App\Enums\ObjectTypeEnum;
use App\Enums\QuestionTypeEnum;
use App\Enums\UserRoleEnum;
use App\Models\Question;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 1,
            'name' => 'Qurilish maydonida kesiladigan daraxt mavjud emasligi',
            'type' => QuestionTypeEnum::COMMON,
            'role_id' => UserRoleEnum::TEXNIK,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 1,
            'name' => 'Ko’chirish kerak bo’lgan kommunikatsiya tarmoqlari mavjudligi',
            'type' => QuestionTypeEnum::COMMON,
            'role_id' => UserRoleEnum::TEXNIK,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 1,
            'name' => 'Qurilish obyektining qo’shni (mavjud) binolarga tasiri',
            'type' => QuestionTypeEnum::COMMON,
            'role_id' => UserRoleEnum::TEXNIK,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 1,
            'name' => 'Texnik va yong\'in xavfsizlik burchagi mavjudligi',
            'type' => QuestionTypeEnum::COMMON,
            'role_id' => UserRoleEnum::TEXNIK,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 1,
            'name' => 'Qurilish obyektining pasporti mavjudligi va u ko‘rinarli joyda o‘rnatilganligi',
            'type' => QuestionTypeEnum::COMMON,
            'role_id' => UserRoleEnum::TEXNIK,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 2,
            'name' => 'Kotlovan geometriyasi',
            'type' => QuestionTypeEnum::BLOCK,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 2,
            'name' => 'Tuproq sinov natijalari ',
            'type' => QuestionTypeEnum::BLOCK,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 3,
            'name' => 'Armokarkaz ishlari ',
            'type' => QuestionTypeEnum::BLOCK,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 3,
            'name' => 'Beton ishlari',
            'type' => QuestionTypeEnum::BLOCK,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 3,
            'name' => 'Izolyatsiya ishlari ',
            'type' => QuestionTypeEnum::BLOCK,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 3,
            'name' => 'Zazemleniya',
            'type' => QuestionTypeEnum::BLOCK,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 4,
            'name' => 'Otmetka kiritiladi ',
            'type' => QuestionTypeEnum::BLOCK,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 4,
            'name' => 'Vertikal otmetkaning to\'g\'ri topilganligi ',
            'type' => QuestionTypeEnum::BLOCK,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 4,
            'name' => 'Ustunlar geometriyasi ',
            'type' => QuestionTypeEnum::BLOCK,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 4,
            'name' => 'Devor ishlari ',
            'type' => QuestionTypeEnum::BLOCK,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 4,
            'name' => 'Rigellar geometriyasi',
            'type' => QuestionTypeEnum::BLOCK,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 4,
            'name' => 'Armokarkaz ishlari ',
            'type' => QuestionTypeEnum::BLOCK,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 4,
            'name' => 'Beton ishlari ',
            'type' => QuestionTypeEnum::BLOCK,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 4,
            'name' => 'Zina o’lchamlari va geometriyasi',
            'type' => QuestionTypeEnum::BLOCK,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 4,
            'name' => 'Eshik, deraza va shaxtalar geometriyasi',
            'type' => QuestionTypeEnum::BLOCK,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 5,
            'name' => 'Tom geometriyasi',
            'type' => QuestionTypeEnum::BLOCK,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 5,
            'name' => 'Tomning izolyatsiya ishlari',
            'type' => QuestionTypeEnum::BLOCK,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 5,
            'name' => 'Yomg\'ir suvlari tizimi',
            'type' => QuestionTypeEnum::BLOCK,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 6,
            'name' => 'Issiqlik ximoyasi',
            'type' => QuestionTypeEnum::BLOCK,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 6,
            'name' => 'Suvoq ishlari',
            'type' => QuestionTypeEnum::BLOCK,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 6,
            'name' => 'Bo\'yoq ',
            'type' => QuestionTypeEnum::BLOCK,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 6,
            'name' => 'Fasad qoplamasi montaji ',
            'type' => QuestionTypeEnum::BLOCK,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 6,
            'name' => 'Tashqi derazalar',
            'type' => QuestionTypeEnum::BLOCK,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 6,
            'name' => 'Tashqi eshiklar',
            'type' => QuestionTypeEnum::BLOCK,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 7,
            'name' => 'Orabo\'lmalar geometriyasi va montaji',
            'type' => QuestionTypeEnum::BLOCK,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 7,
            'name' => 'Suvoq (yoki tayyor qoplama)',
            'type' => QuestionTypeEnum::BLOCK,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 7,
            'name' => 'Bo\'yoq ishlari',
            'type' => QuestionTypeEnum::BLOCK,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 7,
            'name' => 'Kafel geometriyasi va montaji',
            'type' => QuestionTypeEnum::BLOCK,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 7,
            'name' => 'Pol geometriyasi va montaji',
            'type' => QuestionTypeEnum::BLOCK,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 8,
            'name' => 'Ventilyatsiya tizimi montaji',
            'type' => QuestionTypeEnum::BLOCK,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 8,
            'name' => 'Sovutish va isitish tizimi montaji',
            'type' => QuestionTypeEnum::BLOCK,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 8,
            'name' => 'Ichimlik va oqava suv tizimi montaji',
            'type' => QuestionTypeEnum::BLOCK,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 8,
            'name' => 'Tabiiy gaz montaji',
            'type' => QuestionTypeEnum::BLOCK,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 9,
            'name' => 'Yuqori kuchlanishli kabellar montaji',
            'type' => QuestionTypeEnum::BLOCK,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 9,
            'name' => 'Past kuchlanishli kabellar montaji',
            'type' => QuestionTypeEnum::BLOCK,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 9,
            'name' => 'Elektr taqsimot paneli montaji',
            'type' => QuestionTypeEnum::BLOCK,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 9,
            'name' => 'Elektr uskunalari montaji',
            'type' => QuestionTypeEnum::BLOCK,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 10,
            'name' => 'Yong\'indan ogohlantirish tizimi montaji',
            'type' => QuestionTypeEnum::BLOCK,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 10,
            'name' => 'Qo\'riqlash tizimi',
            'type' => QuestionTypeEnum::BLOCK,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 10,
            'name' => 'Internet tarmog\'i',
            'type' => QuestionTypeEnum::BLOCK,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::BUILDING,
            'work_type_id' => 10,
            'name' => 'Videokuzatuv tizimi',
            'type' => QuestionTypeEnum::BLOCK,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::LINEAR,
            'work_type_id' => 11,
            'name' => 'Qurilish maydonida kesiladigan daraxt mavjud emasligi',
            'type' => QuestionTypeEnum::COMMON,
            'role_id' => UserRoleEnum::TEXNIK,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::LINEAR,
            'work_type_id' => 11,
            'name' => 'Ko\'chirish kerak bo\'lgan kommunikatsiya tarmoqlari mavjudligi',
            'type' => QuestionTypeEnum::COMMON,
            'role_id' => UserRoleEnum::TEXNIK,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::LINEAR,
            'work_type_id' => 11,
            'name' => 'Qurilish obyektining qo\'shni (mavjud) binolarga tasiri',
            'type' => QuestionTypeEnum::COMMON,
            'role_id' => UserRoleEnum::TEXNIK,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::LINEAR,
            'work_type_id' => 11,
            'name' => 'Texnik va yong\'in xavfsizlik burchagi mavjudligi',
            'type' => QuestionTypeEnum::COMMON,
            'role_id' => UserRoleEnum::TEXNIK,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::LINEAR,
            'work_type_id' => 11,
            'name' => 'Qurilish obyektining pasporti mavjudligi va u ko\'rinarli joyda o\'rnatilganligi',
            'type' => QuestionTypeEnum::COMMON,
            'role_id' => UserRoleEnum::TEXNIK,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::LINEAR,
            'work_type_id' => 12,
            'name' => 'Kotlovan geometriyasi',
            'type' => QuestionTypeEnum::LINEAR,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::LINEAR,
            'work_type_id' => 12,
            'name' => 'Suv chiqindisi',
            'type' => QuestionTypeEnum::LINEAR,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::LINEAR,
            'work_type_id' => 12,
            'name' => 'Tuproq sinovi labaratoriya xulosasi',
            'type' => QuestionTypeEnum::LINEAR,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::LINEAR,
            'work_type_id' => 12,
            'name' => 'Quvur (kabel) osti ximoya ishlari',
            'type' => QuestionTypeEnum::LINEAR,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::LINEAR,
            'work_type_id' => 12,
            'name' => 'Quvurlar (kabellar) geometriyasi',
            'type' => QuestionTypeEnum::LINEAR,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::LINEAR,
            'work_type_id' => 12,
            'name' => 'Quvurlar (kebellar) izolyatsiyasi',
            'type' => QuestionTypeEnum::LINEAR,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::LINEAR,
            'work_type_id' => 12,
            'name' => 'Quvurlar (kabellar) oraliq masofasi',
            'type' => QuestionTypeEnum::LINEAR,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::LINEAR,
            'work_type_id' => 12,
            'name' => 'Ulanish nuqtalari va quduqlar',
            'type' => QuestionTypeEnum::LINEAR,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::LINEAR,
            'work_type_id' => 12,
            'name' => 'Sinov natijalari',
            'type' => QuestionTypeEnum::LINEAR,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::LINEAR,
            'work_type_id' => 12,
            'name' => 'Quvur (kabel)ning tashqi himoya qatlami',
            'type' => QuestionTypeEnum::LINEAR,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::LINEAR,
            'work_type_id' => 12,
            'name' => 'Tuproq zichligi talablari',
            'type' => QuestionTypeEnum::LINEAR,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::LINEAR,
            'work_type_id' => 12,
            'name' => 'Maydonni loyihaviy qayta tiklash',
            'type' => QuestionTypeEnum::LINEAR,
            'role_id' => UserRoleEnum::ICHKI,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::LINEAR,
            'work_type_id' => 13,
            'name' => 'Qurilishni tashkil qilish talablari',
            'type' => QuestionTypeEnum::MULTIPLY,
        ]);

        Question::query()->create([
            'object_type_id' => ObjectTypeEnum::LINEAR,
            'work_type_id' => 13,
            'name' => 'Qurilishda xavfsizlik texnikasi talablariga rioya qilinishi',
            'type' => QuestionTypeEnum::MULTIPLY,
        ]);
    }
}
