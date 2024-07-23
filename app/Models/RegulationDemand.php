<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegulationDemand extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function actStatus(): BelongsTo
    {
        return $this->belongsTo(ActStatus::class);
    }
}
