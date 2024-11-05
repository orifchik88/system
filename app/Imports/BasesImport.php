<?php

namespace App\Imports;

use App\Models\Basis;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BasesImport implements ToModel, WithHeadingRow
{
    /**
    * @param Collection $collection
    */
    public function model(array $row)
    {
        return new Basis([
            'parent_id' => $row['parent_id'],
            'name' => $row['name'],
        ]);
    }
}
