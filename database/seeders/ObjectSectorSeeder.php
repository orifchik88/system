<?php

namespace Database\Seeders;

use App\Models\ObjectSector;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ObjectSectorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            "Uy-joylar (PF-5886)",
            "Tashabbusli byudjet",
            "Noqonuniy qurilish obyektlari",
            "Investitsiya dasturi 2022 PQ-98",
            "Milliy Gvardiya, Mudofaa, FVV, IIV vazirliklari, MXX va Bosh Prokuratura buyurtmachiligidagi obyektlari",
            "PF-6186, PF-6274 va 149-rayyosat",
            "\"Obod qishloq\" va \"Obod mahalla\"",
            "Yangi O‘zbekiston",
            "Boshqalar",
            "Kichik sanoat zonalari",
            "Mahallalar infratuzilmasi PQ-408",
            "Investitsiya dasturi 2023 PQ-465",
            "Investitsiya loyihalarini boshqarishning yangi yondashuv va mexanizmlarini joriy etish PQ-72",
            "Yirik sanoat PQ-459",
            "Paralel loyihalash obyektlari",
            "Uy-joy kommunal xizmat ko‘rsatish vazirligi va Suv xo‘jaligi vazirligi buyurtmachiligidagi obyektlar",
            "Open byudjet PQ-409",
            "2021 yil \"Obod kishlok\" va \"Obod maxalla\" dasturi obyektlari (PK-5048)",
            "2021 yil Xorijiy investitsiyalar va kreditlar hisobiga qurilayotgan obyektlari (PQ-4937)",
            "2020 yil ijtimoiy soha obyektlari (PQ-4565)",
            "Prezident, Bosh vazir va Bosh vazir o‘rinbosarlari tashrifi",
            "Mahallalar infratuzulmasini rivojlantirish (PQ-408)",
            "VM-552 \"Qishloq joylarni barqaror rivojlantirish\"",
            "Melioratsiya obyektlarini tizimli taʼmirlash-tiklash",
            "Mening yo‘lim loyihasi",
            "Obod qishloq va Obod mahalla (2023)",
            "PF-51 Farmon, VM-108-bayon",
            "Bozor tamoyillariga asoslangan ipoteka kreditlari orqali aholini uy-joy bilan taʼminlashga oid qo‘shimcha chora-tadbirlar to‘g‘risida PQ-33",
            "Melioratsiya obyektlarini tizimli taʼmirlash-tiklash",
            "Ijod va ixtisoslashtirilgan maktablar",
            "Tuman markazlari va shaharlarning meʼmoriy qiyofasini zamonaviylashtirish va ...",
            "Tadbirkorlik tomonidan shaharlarda va qishloq joylarda ko‘p kvartirali uy-joy qurish (VM-22, 149-rayyosat))",
            "Ilm-fan va innovatsiya",
            "Tadbirkorlikni rivojlantirish jamg‘armasi loyihalari",
            "Hududlar va tarmoqlarni ijtimoiy-iqtisodiy rivojlantirish",
            "Mahallalar infratuzilmasini rivojlantirish",
            "Investitsiya dasturi 2024 PQ-404",
            "Maʼmuriy, muhandislik-infratuzilma, alohida muhim va toifalangan obyektlar",
            "Avtomobil yo‘llari va yo‘l o‘tkazgichlar",
            "PQ-465 ijtimoiy soha",
            "Umumiy foydalanishdagi avtomobil yullarini mukammal taʼmirlash",
            "Ko‘p qavatli uy joylar qurilishi va ularni modernizatsiya qilish",
            "Mahallalar infratuzilmasini rivojlantirish (alohida muhim topshiriq)",
            "Joriy(mukammal) taʼmirlanadigan obyektlar",
            "Turizm obyektlari",
            "Boshqa",
            "Vazirlar Mahkamasining zaxira jamgʼarmasi",
            "Qirg‘oq bo‘yi va boshqa himoya inshootlar",
            "Mahalliy byudjet joriy (mukammal) tamirlash obyektlari",
            "Vazirlik va idorlarning byudjetdan tashqari jamgʼarmalari",
            "Mahalliy byudjet mablagʼlari hisobidan amalga oshiriladigan loyihalar",
            "Open byudjet",
            "Ijtimoiy va ishlab chiqarish infratuzulmasini rivojlantirish",
            "«Yangi O‘zbekiston» massivlarida barpo etiladigan infratuzilma obyektlari uchun",
            "Umumiy foydalanishdagi avtomobil yullari",
            "Vazirlik va idorlarning byudjetdan tashqari jamg‘armalari",
            "Tadbirkorlikni rivojlantirish jamg‘armasi loyihalari",
            "Tabiiy ofat okibatlarini bartaraf etish obyektlari",
            "Qirg‘oq bo‘yi va boshqa himoya inshootlar",
            "Temir yo‘l va metro transporti",
            "Obod qishloq va obod maxalla",
            "Mening yo‘lim loyihasi (Ovoz berish orqali shakllangan)",
            "Maxsus iqtisodiy zonalar va kichik sanoat zonalarida. shuningdek qiymati kamida 200 mlrd so‘mdan ortiq bo‘lgan investitsiya loyihalarning zarur infratuzilmani qurish",
            "Gidrometeorologik stansiyalarni qurish va rekonstruksiya qilish",
            "Gaz tarmog‘ini qurish",
            "Hududlar va tarmoqlarni ijtimoiy-iqtisodiy rivojlantirish",
            "Vazirlar Mahkamasining zaxira jamg‘armasi",
            "Mahalliy byudjet mablag‘lari hisobidan amalga oshiriladigan loyihalar",
            "Davlat-xususiy sheriklik shartlari asosida loyihalarni amalga oshirishni birgalikda moliyalashtirish",
            "Open byudjet 2024"
        ];

        ObjectSector::query()->truncate();

        ObjectSector::create([
            'name_uz' =>"Tadbirkorlik (xususiy) obyektlari",
            'name_ru' =>"Tadbirkorlik (xususiy) obyektlari",
            'funding_source_id' =>2,
        ]);


        foreach ($data as $datum) {
            ObjectSector::create([
                'name_uz' => $datum,
                'name_ru' => $datum,
                'funding_source_id' => 1,
            ]);
        }
    }
}
