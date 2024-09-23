<?php

namespace App\Models;

use App\Enums\LevelStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class Level extends Model
{
    use HasFactory, Searchable;

    protected $guarded = false;

    protected $casts = [
        'level_status_id' => LevelStatusEnum::class
    ];

    public function toSearchableArray()
    {
        return [
            'name' => $this->name
        ];
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(Checklist::class);
    }

    public function levelStatus(): BelongsTo
    {
        return $this->belongsTo(LevelStatus::class);
    }
}
