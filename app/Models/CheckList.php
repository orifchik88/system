<?php

namespace App\Models;

use App\Enums\ObjectTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class CheckList extends Model
{
    use HasFactory;

    protected $guarded = false;

    protected $casts = [
        'object_type_id' => ObjectTypeEnum::class,
    ];

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}
