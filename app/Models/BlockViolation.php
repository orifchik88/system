<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class BlockViolation extends Model
{
    use HasFactory;

    protected $guarded = [];

    public $timestamps =false;

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function blockUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'block_users', 'block_violation_id', 'user_id')
            ->withPivot('role_id', 'block_id');
    }
}
