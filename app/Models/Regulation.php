<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Regulation extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function regulationStatus(): BelongsTo
    {
        return $this->belongsTo(RegulationStatus::class);
    }

    public function regulationType(): BelongsTo
    {
        return $this->belongsTo(RegulationType::class);
    }

    public function actStatus(): BelongsTo
    {
        return $this->belongsTo(ActStatus::class);
    }

    public function violations(): BelongsToMany
    {
        return $this->belongsToMany(Violation::class, 'regulation_violations', 'regulation_id', 'violation_id');
    }




}
