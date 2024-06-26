<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class DxaResponse extends Model
{
    protected  $guarded =[];
    protected  $fillable = ['url'];

    use HasFactory;

    public function supervisors(): HasMany
    {
        return $this->hasMany(DxaResponseSupervisor::class);
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}
