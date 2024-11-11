<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ArticleHistory extends Model
{
    use HasFactory;

    protected $table = 'article_histories';

    protected $guarded = false;

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

}
