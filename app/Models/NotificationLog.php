<?php

namespace App\Models;

use App\Enums\NotificationTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    use HasFactory;

    protected $guarded = false;

    protected $casts = [
        'type' => NotificationTypeEnum::class,
        'additional_info' => 'array'
    ];
}
