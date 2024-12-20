<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IllegalObjectImage extends Model
{
    use HasFactory;

    protected $table = 'illegal_objects_images';
    protected $guarded = false;
}
