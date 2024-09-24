<?php

namespace Database\Seeders;

use App\Models\Basis;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BasisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Basis::query()->truncate();
        $file = storage_path() . "/sql/asos.sql";

        $basis = file_get_contents($file);

        DB::unprepared($basis);
    }
}
