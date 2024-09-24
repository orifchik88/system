<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NormativeDocument extends Model
{
    use HasFactory;

    protected $guarded = false;

    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class, 'parent_id');
    }

}
