<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class IllegalObjectCheckList extends Model
{
    use HasFactory;

    protected $table = 'illegal_objects_checklist';
    protected $guarded = false;

    public function question(): BelongsTo
    {
        return $this->belongsTo(IllegalObjectQuestion::class, 'question_id', 'id');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(IllegalObjectCheckListHistory::class, 'gu_id', 'id');
    }
}
