<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sphere extends Model
{
    use HasFactory;

    protected $guarded = false;

    public function responses(): HasMany
    {
        return $this->hasMany(DxaResponse::class, 'sphere_id');
    }

    public function scopeSearchByName($query, $searchTerm)
    {
        $searchTerm = strtolower($searchTerm);

        return $query->whereRaw('LOWER(name_uz) LIKE ?', ['%' . $searchTerm . '%']);

    }
}
