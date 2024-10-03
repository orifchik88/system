<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class RegulationDemand extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function actStatus(): BelongsTo
    {
        return $this->belongsTo(ActStatus::class);
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function actViolation(): BelongsTo
    {
        return $this->belongsTo(ActViolation::class);
    }
}
