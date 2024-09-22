<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckList extends Model
{
    use HasFactory;

    protected $guarded = false;

    protected $casts = [
        'object_type_id' => ObjectType::class,
    ];
}
