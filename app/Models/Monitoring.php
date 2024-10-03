<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Monitoring extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function regulationType(): BelongsTo
    {
        return $this->belongsTo(RegulationType::class, 'regulation_type_id');
    }

    public function regulations(): HasMany
    {
        return $this->hasMany(Regulation::class, 'monitoring_id');
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(CheckListAnswer::class, 'monitoring_id');
    }

    public function object(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'object_id');
    }


    public function actViolations(): HasManyThrough
    {
        return $this->hasManyThrough(ActViolation::class, RegulationViolationBlock::class, 'regulation_id', 'violation_id', 'id', 'violation_id');
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class, 'block_id');
    }
}
