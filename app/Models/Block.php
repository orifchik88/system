<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Block extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($block) {
            $lastBlock = Block::query()->orderBy('block_number', 'desc')->first();
            if ($lastBlock->notification_type == 1)
                $lastNumber = $lastBlock ? $lastBlock->block_number : 999999;
                $block->block_number = $lastNumber + 1;

        });
    }

    public function object(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'article_id');
    }

    public function responses(): BelongsToMany
    {
        return $this->belongsToMany(DxaResponse::class, 'dxa_response_blocks', 'block_id', 'dxa_response_id');
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
