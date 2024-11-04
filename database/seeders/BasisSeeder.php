<?php

namespace Database\Seeders;

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
        $path = storage_path('excel/shnq');

        $files = Storage::files('excel/shnq');

        foreach ($files as $file) {
            dd($file);
            // Har bir faylni o'qib, import qilamiz
//            Excel::import(new DataImport, $file);
        }

    }
}
