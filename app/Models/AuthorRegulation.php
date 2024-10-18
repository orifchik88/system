<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthorRegulation extends Model
{
    use HasFactory;

    protected $guarded = false;

    public function object(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'object_id');
    }
    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class, 'block_id');
    }
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function bases(): BelongsTo
    {
        return $this->belongsTo(Basis::class, 'bases_id');
    }

    public function workType(): BelongsTo
    {
        return $this->belongsTo(WorkType::class, 'work_type_id');
    }
}
