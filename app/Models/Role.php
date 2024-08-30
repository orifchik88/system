<?php

namespace App\Models;

use App\Enums\RoleTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'type' => RoleTypeEnum::class,
        'children' => 'array'
    ];
}
