<?php

namespace App\Imports;

use App\Models\Topic;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TopicImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Topic([
            'id' => $row['id'],
            'parent_id' => $row['parent_id'],
            'name' => $row['name'],
        ]);
    }
}
