<?php

namespace App\Models\Traits;

use App\Enums\RegulationStatusEnum;
use App\Models\Article;
use App\Models\User;
use App\Models\Violation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Role;

trait RegulationTrait
{

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            if ($model->regulation_status_id == RegulationStatusEnum::CONFIRM_REMEDY && $model->getOriginal('regulation_status_id') == RegulationStatusEnum::PROVIDE_REMEDY) {
                $model->update([
                    'paused_at' => Carbon::now(),
                ]);
            }

            if (($model->regulation_status_id == RegulationStatusEnum::PROVIDE_REMEDY || $model->regulation_status_id == RegulationStatusEnum::ATTACH_DEED) && $model->getOriginal('regulation_status_id') == RegulationStatusEnum::CONFIRM_REMEDY) {
                if ($model->paused_at) {
                    $elapsedSeconds = Carbon::now()->diffInSeconds(Carbon::parse($model->paused_at));

                    $newDeadline = Carbon::parse($model->previous_deadline)->addSeconds($elapsedSeconds)->toDateTimeString();

                    $model->update([
                        'deadline' => $newDeadline,
                        'paused_at' => null,
                    ]);
                }
            }

            if ($model->regulation_status_id == RegulationStatusEnum::CONFIRM_DEED && $model->getOriginal('regulation_status_id') == RegulationStatusEnum::ATTACH_DEED) {
                $model->update([
                    'paused_at' => Carbon::now(),
                ]);
            }

            if ($model->regulation_status_id == RegulationStatusEnum::ATTACH_DEED && $model->getOriginal('regulation_status_id') == RegulationStatusEnum::CONFIRM_DEED) {
                if ($model->paused_at) {
                    $elapsedSeconds = Carbon::now()->diffInSeconds(Carbon::parse($model->paused_at));

                    $newDeadline = Carbon::parse($model->previous_deadline)->addSeconds($elapsedSeconds)->toDateTimeString();

                    $model->update([
                        'deadline' => $newDeadline,
                        'paused_at' => null,
                    ]);
                }
            }

            if ($model->regulation_status_id == RegulationStatusEnum::CONFIRM_REMEDY && $model->getOriginal('regulation_status_id') != RegulationStatusEnum::CONFIRM_REMEDY) {
                $model->update([
                    'previous_deadline' => $model->deadline,
                ]);
            }
        });
    }


    public function scopeWithInspectorRole(Builder $query): Builder
    {
        return $query->whereHas('role', function($query) {
            $query->whereHas('permissions', function($query) {
                $query->where('name', 'is_inspector');
            });
        });
    }

    public function scopeWithoutInspectorRole(Builder $query): Builder
    {
        return $query->whereDoesntHave('role', function($query) {
            $query->whereHas('permissions', function($query) {
                $query->where('name', 'is_inspector');
            });
        });
    }
    public function violations(): BelongsToMany
    {
        return $this->belongsToMany(Violation::class, 'regulation_violation_blocks', 'regulation_id', 'violation_id')
            ->withPivot('block_id')
            ->distinct('violation_id');
    }

    public function object(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'object_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'created_by_role_id', 'id');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function createdByRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'created_by_role_id');
    }

    public function responsibleRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

}
