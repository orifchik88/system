<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class CheckListHistory extends Model
{
    use HasFactory;

    public $table = 'check_list_histories';

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'content->user');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'content->role');
    }
}
