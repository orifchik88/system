<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Violation extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'violation_users', 'violation_id', 'user_id');
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }


    public function blockViolations(): BelongsToMany
    {
        return $this->belongsToMany(Block::class, 'block_violations');
    }

    public function regulations(): BelongsToMany
    {
        return $this->belongsToMany(Regulation::class, 'regulation_violations', 'violation_id', 'regulation_id');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function blocks()
    {
        return $this->belongsToMany(Block::class, 'regulation_violation_blocks', 'violation_id', 'block_id');
    }

    public function bases(): BelongsTo
    {
        return $this->belongsTo(Basis::class, 'bases_id');
    }
}
