<?php

namespace Database\Seeders;

use App\Models\ObjectType;
use Illuminate\Database\Seeder;

class ObjectTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ObjectType::create(['name' => 'Tarmoqli', 'description' => 'Tarmoqli Objekt']);
        ObjectType::create(['name' => 'Bino', 'description' => 'Bino Inshoat']);
    }
}
