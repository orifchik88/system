<?php

namespace Database\Seeders;

use App\Imports\BasesImport;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;

class BasisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shnq = storage_path() . "/excel/bases.xlsx";
        Excel::import(new BasesImport(), $shnq);

    }
}
