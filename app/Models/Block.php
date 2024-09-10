<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Block extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function object(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'article_id');
    }

    public function response(): BelongsTo
    {
        return $this->belongsTo(DxaResponse::class, 'dxa_response_id');
    }

    public function mode(): BelongsTo
    {
        return $this->belongsTo(BlockMode::class, 'block_mode_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(BlockType::class, 'block_type_id');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function actViolationBlock(): BelongsTo
    {
        return $this->belongsTo(ActViolationBlock::class, 'block_id');
    }

}
