<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegulationViolation extends Model
{
    use HasFactory;

    protected $guarded = false;

    public $timestamps = false;

    public function regulation(): BelongsTo
    {
        return $this->belongsTo(Regulation::class, 'regulation_id');
    }

    public function violation(): BelongsTo
    {
        return $this->belongsTo(Violation::class, 'violation_id');
    }
}
