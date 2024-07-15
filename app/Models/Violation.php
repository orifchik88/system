<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Violation extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'violation_users', 'violation_id', 'user_id');
    }

    public function blocks(): BelongsToMany
    {
        return $this->belongsToMany(Block::class, 'block_violations', 'violation_id', 'block_id');
    }

    public function regulations(): BelongsToMany
    {
        return $this->belongsToMany(Regulation::class, 'regulation_violations', 'violation_id', 'regulation_id')->withPivot('user_id');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }
}
