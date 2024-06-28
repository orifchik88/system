<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DxaResponse extends Model
{
    use HasFactory, SoftDeletes;

    protected  $guarded =[];
    protected  $fillable = ['url'];


    public function supervisors(): HasMany
    {
        return $this->hasMany(DxaResponseSupervisor::class);
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}
