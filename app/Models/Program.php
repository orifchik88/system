<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function responses(): HasMany
    {
        return $this->hasMany(DxaResponse::class, 'program_id');
    }
}
