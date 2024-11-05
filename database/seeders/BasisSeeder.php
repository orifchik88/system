<?php

namespace Database\Seeders;

use App\Imports\BasesImport;
use App\Models\Basis;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class BasisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shnq = storage_path() . "/excel/shnq.xlsx";
        Excel::import(new BasesImport(), $shnq);

    }
}
