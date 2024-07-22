<?php

namespace App\Models;

use App\Models\Traits\RegulationTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;

class Regulation extends Model
{
    use HasFactory, RegulationTrait;

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





}
