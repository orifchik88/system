<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class RegulationViolationBlock extends Model
{
    use HasFactory;

    protected $guarded = [];

    public $timestamps = false;

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}
