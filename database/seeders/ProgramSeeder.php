<?php

namespace Database\Seeders;

use App\Models\Program;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = getData(config('app.gasn.programs'));
        $programs = $data['data']['data'];

        foreach ($programs as $program) {
            Program::create([
                'id' => $program['id'],
                'name_uz' => $program['name_uz'],
                'name_ru' => $program['name_ru'],
            ]);
        }
    }
}
