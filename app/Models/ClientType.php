<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class ClientType extends Model
{
    use HasFactory, SoftDeletes, Searchable;

    protected $guarded = [];

    public function userType(): BelongsTo
    {
        return $this->belongsTo(UserType::class);
    }

    public function toSearchableArray()
    {
        return [
            'name' => $this->name
        ];
    }
}
