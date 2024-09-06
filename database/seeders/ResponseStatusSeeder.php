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
        DxaResponseStatus::create(['status' => 'Yangi', 'name' => 'new']);
        DxaResponseStatus::create(['status' => 'Inspektorga yuborilgan', 'name' => 'send_inspector']);
        DxaResponseStatus::create(['status' => 'Registratorda', 'name' => 'in_register']);
        DxaResponseStatus::create(['status' => 'Qabul qilingan', 'name' => 'accepted']);
        DxaResponseStatus::create(['status' => 'Rad etilgan', 'name' => 'rejected']);
        DxaResponseStatus::create(['status' => 'Bekor qilingan', 'name' => 'canceled']);
    }
}
