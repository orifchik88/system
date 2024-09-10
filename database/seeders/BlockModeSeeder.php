<?php

namespace Database\Seeders;

use App\Models\BlockMode;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BlockModeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        BlockMode::create(['name' => 'Bino']);
        BlockMode::create(['name' => 'Inshoot']);
        BlockMode::create(['name' => 'Tarmoq']);
    }
}
