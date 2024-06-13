<?php

namespace Database\Seeders;

use App\Models\DxaResponseStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ResponseStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void
    {
        DxaResponseStatus::create(['status' => 'Tekshirilgan', 'name' => 'checked']);
        DxaResponseStatus::create(['status' => 'Arxiv', 'name' => 'archive']);
        DxaResponseStatus::create(['status' => 'Yangi', 'name' => 'new']);
        DxaResponseStatus::create(['status' => 'Qayta rasmiylashtirish', 'name' => 're_registration']);
        DxaResponseStatus::create(['status' => 'Inspektorga yuborilgan', 'name' => 'send_inspector']);
    }
}
