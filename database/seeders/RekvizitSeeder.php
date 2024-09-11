<?php

namespace Database\Seeders;

use App\Imports\RekvizitImport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;

class RekvizitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $rekvizit = storage_path() . "/excel/rekvizit.xlsx";
        Excel::import(new RekvizitImport(), $rekvizit);
    }
}
