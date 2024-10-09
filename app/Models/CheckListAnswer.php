<?php

namespace App\Models;

use App\Enums\CheckListStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class CheckListAnswer extends Model
{
    use HasFactory;
    protected $guarded = false;

    protected $casts = [
        'status' => CheckListStatusEnum::class,
    ];

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'question_id');
    }

    public function workType(): BelongsTo
    {
        return $this->belongsTo(WorkType::class, 'work_type_id');
    }

    public function object(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'object_id');
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class, 'block_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(CheckListHistory::class, 'gu_id');
    }
}
