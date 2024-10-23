<?php

namespace App\Models;

use App\Enums\UserHistoryTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserHistory extends Model
{
    use HasFactory;

    protected $guarded = false;

    protected $casts = [
        'content' => 'object'
    ];
}
