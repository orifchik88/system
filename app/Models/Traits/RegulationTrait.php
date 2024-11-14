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

//    protected static function boot()
//    {
//        parent::boot();
//
//        static::saved(function ($model) {
//            // Bir necha martalik update bo'lishining oldini olish uchun o'zgarganligini tekshirish
//            if ($model->isDirty('regulation_status_id')) {
//
//                // Status CONFIRM_REMEDY ga o'zgarganda paused_at ni belgilash
//                if ($model->regulation_status_id == RegulationStatusEnum::CONFIRM_REMEDY &&
//                    $model->getOriginal('regulation_status_id') == RegulationStatusEnum::PROVIDE_REMEDY) {
//
//                    $model->paused_at = Carbon::now();
//                    $model->saveQuietly(); // saveQuietly qayta saved event ni chaqirmaydi
//                }
//
//                // Status PROVIDE_REMEDY yoki ATTACH_DEED ga qaytganda, deadline davom etadi
//                if (($model->regulation_status_id == RegulationStatusEnum::PROVIDE_REMEDY ||
//                        $model->regulation_status_id == RegulationStatusEnum::ATTACH_DEED) &&
//                    $model->getOriginal('regulation_status_id') == RegulationStatusEnum::CONFIRM_REMEDY) {
//
//                    if ($model->paused_at) {
//                        $elapsedSeconds = Carbon::now()->diffInSeconds(Carbon::parse($model->paused_at));
//                        $model->deadline = Carbon::parse($model->previous_deadline)->addSeconds($elapsedSeconds)->toDateTimeString();
//                        $model->paused_at = null;
//                        $model->saveQuietly();
//                    }
//                }
//
//                // Status CONFIRM_DEED ga o'zgarganda paused_at ni belgilash
//                if ($model->regulation_status_id == RegulationStatusEnum::CONFIRM_DEED &&
//                    $model->getOriginal('regulation_status_id') == RegulationStatusEnum::ATTACH_DEED) {
//
//                    $model->paused_at = Carbon::now();
//                    $model->saveQuietly();
//                }
//
//                // Status ATTACH_DEED ga qaytganda deadline davom etadi
//                if ($model->regulation_status_id == RegulationStatusEnum::ATTACH_DEED &&
//                    $model->getOriginal('regulation_status_id') == RegulationStatusEnum::CONFIRM_DEED) {
//
//                    if ($model->paused_at) {
//                        $elapsedSeconds = Carbon::now()->diffInSeconds(Carbon::parse($model->paused_at));
//                        $model->deadline = Carbon::parse($model->previous_deadline)->addSeconds($elapsedSeconds)->toDateTimeString();
//                        $model->paused_at = null;
//                        $model->saveQuietly();
//                    }
//                }
//
//                // Status CONFIRM_REMEDY ga o'zgarganda previous_deadline saqlash
//                if ($model->regulation_status_id == RegulationStatusEnum::CONFIRM_REMEDY &&
//                    $model->getOriginal('regulation_status_id') != RegulationStatusEnum::CONFIRM_REMEDY) {
//
//                    $model->previous_deadline = $model->deadline;
//                    $model->saveQuietly();
//                }
//            }
//        });
//    }


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
