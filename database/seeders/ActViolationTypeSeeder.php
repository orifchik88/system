<?php

namespace Database\Seeders;

use App\Models\ActViolationType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ActViolationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $meta = [
            'Chora tadbir',
            'Dalolatnoma',
            'Muddat uzaytirish'
        ];

        foreach ($meta as  $value) {
            ActViolationType::create(['type' => $value]);
        }
    }
}
