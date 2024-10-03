<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ActViolation extends Model
{
    use HasFactory;

    const PROGRESS = 1;
    const REJECTED = 2;
    const ACCEPTED = 3;


    protected $guarded = [];

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function demands(): HasMany
    {
        return $this->hasMany(RegulationDemand::class, 'act_violation_id');
    }

    public function violation(): BelongsTo
    {
        return $this->belongsTo(Violation::class);
    }

    public function regulationViolation(): BelongsTo
    {
        return $this->belongsTo(RegulationViolation::class);
    }

    public function blocks(): BelongsToMany
    {
        return $this->belongsToMany(Block::class, 'act_violation_blocks', 'act_violation_id', 'block_id')
            ->withPivot('comment')
            ->with('documents');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function actStatus(): BelongsTo
    {
        return $this->belongsTo(ActStatus::class, 'act_status_id');
    }
}
