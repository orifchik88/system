<?php

namespace App\Models;

use App\Enums\LevelStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Level extends Model
{
    use HasFactory, Searchable;

    protected $guarded = [];

    protected $casts = [
        'level_status_id' => LevelStatusEnum::class
    ];

    public function toSearchableArray()
    {
        return [
            'name' => $this->name
        ];
    }
}
