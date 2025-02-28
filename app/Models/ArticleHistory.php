<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ArticleHistory extends Model
{
    use HasFactory;

    protected $table = 'article_histories';

    protected $guarded = false;

    protected $casts = [
        'content' => 'object'
    ];

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }


    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getUserIdAttribute()
    {
        return $this->content->user ?? null;
    }

    public function getRoleIdAttribute()
    {
        return $this->content->role ?? null;
    }

}
