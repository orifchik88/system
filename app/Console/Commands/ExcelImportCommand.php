<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ExcelImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:excel-import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $files = [
            'Suv.xlsx',
            'Kvartiralar.xlsx',
            'Noqonuniy.xlsx'
        ];

        $currentId = DB::table('illegal_objects_questions')->max('id') ?? 0;

        foreach ($files as $file) {
            $filePath = storage_path() . "/excel/illegal/{$file}";

            $data = Excel::toCollection(null, $filePath);

            foreach ($data[0] as $key=>$row) {
                if ($key === 0) {
                    continue;
                }

                $currentId++;
                DB::table('illegal_objects_questions')->insert([
                    'id' =>  $currentId,
                    'name' => $row['1'],
                    'role' => $row['2'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

    }
}
