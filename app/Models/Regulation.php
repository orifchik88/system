<?php

namespace App\Models;

use App\Enums\LawyerStatusEnum;
use App\Enums\RegulationStatusEnum;
use App\Models\Traits\RegulationTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Regulation extends Model
{
    use HasFactory, RegulationTrait;

    protected $guarded = [];

    protected $casts = [
        'lawyer_status_id' => LawyerStatusEnum::class,
        'regulation_status_id' => RegulationStatusEnum::class,
    ];

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

    public function regulationUser(): HasOne
    {
        return $this->hasOne(RegulationUser::class, 'regulation_id');
    }

    public function actViolations(): HasMany
    {
        return $this->hasMany(ActViolation::class);
    }

    public function object(): BelongsTo
    {
        return  $this->belongsTo(Article::class, 'object_id');
    }

    protected function isOld(): Attribute
    {
        return Attribute::make(
            get: fn () => strpos($this->regulation_number, '-') !== false || strpos($this->regulation_number, '/') !== false,
        );
    }

    public function demands() : HasMany
    {
        return $this->hasMany(RegulationDemand::class);
    }

    public function violations(): BelongsToMany
    {
        return $this->belongsToMany(Violation::class, 'regulation_violations');
    }

    public function monitoring(): BelongsTo
    {
        return $this->belongsTo(Monitoring::class);
    }

    public function regulationViolations(): HasMany
    {
        return $this->hasMany(RegulationViolation::class, 'regulation_id');
    }

    public function fines(): HasMany
    {
        return $this->hasMany(RegulationFine::class, 'regulation_id');
    }

    public function fine(): HasOne
    {
        return $this->hasOne(RegulationFine::class, 'regulation_id')->latest();
    }

    public function lawyerStatus(): BelongsTo
    {
        return $this->belongsTo(LawyerStatus::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(CheckListAnswer::class, 'checklist_id');
    }








}
