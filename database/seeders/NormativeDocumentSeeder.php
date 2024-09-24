<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NormativeDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $file = storage_path() . "/sql/normativ.sql";

        $normative = file_get_contents($file);
        dd($normative);

    }
}
