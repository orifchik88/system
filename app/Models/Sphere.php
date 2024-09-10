<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sphere extends Model
{
    use HasFactory;

    public function responses(): HasMany
    {
        return $this->hasMany(DxaResponse::class, 'sphere_id');
    }
}
