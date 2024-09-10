<?php

namespace Database\Seeders;

use App\Models\BlockType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BlockTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bino = [
            'Ko\'p xonadonli turar joy binosi',
            'Yordamchi bino',
            'Savdo va xizmat ko\'rsatish binosi',
            'Ishlab chiqarish binosi',
            'Ma’muriy bino',
            'Ta\'lim binosi',
            'Sog‘liqni saqlash binosi',
            'Sport binosi',
            'Ommaviy bino',
            'Qishloq xo‘jaligi binosi',
            'Boshqa',
        ];

        $inshoot = [
            'Suv saqlash inshooti',
            'Suv tozalash inshooti',
            'Gaz taqsimlash stansiyasi',
            'Nasos stansiyasi',
            'Elektr podstansiyasi',
            'Gidrotexnik inshoot (to‘g‘on, suv ombori)',
            'Suv chiqarish qudug‘i',
            'Boshqa'
        ];

        $tarmoq = [
            'Ichimlik suv tarmog\'i',
            'Oqava suv tarmog\'i',
            'Elektr tarmoqlari',
            'Gaz tarmoqlari',
            'Boshqa',
        ];

        foreach ($bino as $item) {
            BlockType::query()->create(['name' => $item, 'block_mode_id' => 1]);
        }

        foreach ($inshoot as $item) {
            BlockType::query()->create(['name' => $item, 'block_mode_id' => 2]);
        }

        foreach ($tarmoq as $item) {
            BlockType::query()->create(['name' => $item, 'block_mode_id' => 3]);
        }

    }
}
