<?php

namespace App\Imports;

use App\Models\Rekvizit;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class RekvizitImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Rekvizit([
            'region_id' => $row['region_id'],
            'name' => $row['name'],
        ]);
    }
}
