<?php

namespace Database\Seeders;

use App\Imports\BasesImport;
use App\Imports\TopicImport;
use App\Models\Topic;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class TopicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $shnq = storage_path() . "/excel/topic.xlsx";
        Excel::import(new TopicImport(), $shnq);
//        $file = storage_path() . "/sql/mavzu.sql";
//
//        $topic = file_get_contents($file);
//
//        DB::unprepared($topic);
    }
}
