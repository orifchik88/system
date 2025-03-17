<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class IllegalObject extends Model
{
    use HasFactory;

    protected $table = 'illegal_objects';
    protected $guarded = false;

    protected $casts = [
        'score' => 'array'
    ];

    public function getQuestionTypeAttribute()
    {
        $scores = $this->score ?? [];
        foreach ($scores as &$score) {
            $score['type_name'] = IllegalQuestionType::find($score['type'])->name ?? null;
        }
        return $scores;
    }

    public function images()
    {
        return $this->hasMany(IllegalObjectImage::class, 'illegal_object_id');
    }
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id', 'id')->select('name_uz', 'soato', 'id');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_id', 'id')->select('name_uz', 'soato', 'id');
    }

    public function histories() : HasMany
    {
        return $this->hasMany(IllegalObjectHistory::class, 'gu_id');
    }
}
