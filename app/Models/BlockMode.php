<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlockMode extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function blockTypes(): HasMany
    {
        return $this->hasMany(BlockType::class);
    }
}
