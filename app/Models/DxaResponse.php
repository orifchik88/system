<?php

namespace App\Models;

use App\Enums\DxaResponseStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DxaResponse extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
//    protected  $fillable = ['url'];

    protected $casts = [
        'dxa_response_status_id' => DxaResponseStatusEnum::class
    ];


    public function supervisors(): HasMany
    {
        return $this->hasMany(DxaResponseSupervisor::class);
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(DxaResponseStatus::class, 'dxa_response_status_id');
    }

    public function administrativeStatus(): BelongsTo
    {
        return $this->belongsTo(AdministrativeStatus::class, 'administrative_status_id');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class, 'dxa_response_id');
    }

    public function fundingSource(): BelongsTo
    {
        return $this->belongsTo(FundingSource::class, 'funding_source_id');
    }

    public function sphere(): BelongsTo
    {
        return $this->belongsTo(Sphere::class, 'sphere_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id');
    }
}
