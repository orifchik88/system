<?php

namespace App\Models\Traits;

use App\Models\Article;
use App\Models\Role;
use App\Models\User;
use App\Models\Violation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\URL;

trait RegulationTrait
{
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

    public function getPdfAttribute()
    {
        return URL::to('regulation-info/'.$this->id);
    }

}
