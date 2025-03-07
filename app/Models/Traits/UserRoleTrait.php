<?php

namespace App\Models\Traits;

use App\Enums\UserRoleEnum;
use App\Models\User;
use App\Models\UserEmployee;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait UserRoleTrait
{

    public function roleObjects($roleId)
    {
        return $this->objects()->where('role_id', $roleId);
    }
    public function kadr(): bool
    {
        return $this->roles()->where('role_id', UserRoleEnum::RESKADR->value)->exists();
    }

    public function regionKadr(): bool
    {
        return $this->roles()->where('role_id', UserRoleEnum::REGKADR->value)->exists();
    }

    public function inspector(): bool
    {
        return $this->roles()->where('role_id', UserRoleEnum::INSPECTOR->value)->exists();
    }
    public function register(): bool
    {
        return $this->roles()->where('role_id', UserRoleEnum::REGISTRATOR->value)->exists();
    }

    public function ichki(): bool
    {
        return $this->roles()->where('role_id', UserRoleEnum::ICHKI->value)->exists();
    }

    public function texnik(): bool
    {
        return $this->roles()->where('role_id', UserRoleEnum::TEXNIK->value)->exists();
    }

    public function muallif(): bool
    {
        return $this->roles()->where('role_id', UserRoleEnum::MUALLIF->value)->exists();
    }

    public function buyurtmachi(): bool
    {
        return  $this->roles()->where('role_id', UserRoleEnum::BUYURTMACHI->value)->exists();
    }

    public function loyiha(): bool
    {
        return $this->roles()->where('role_id', UserRoleEnum::LOYIHA->value)->exists();
    }

    public function qurilish(): bool
    {
        return $this->roles()->where('role_id', UserRoleEnum::QURILISH->value)->exists();
    }

    public function operator(): bool
    {
        return $this->roles()->where('role_id', 11)->exists();
    }

    public function lead(): bool
    {
        return $this->roles()->where('role_id', 12)->exists();
    }

    public function viewerRegion(): bool
    {
        return $this->roles()->where('role_id', 13)->exists();
    }
    public function viewer(): bool
    {
        return $this->roles()->where('role_id', 14)->exists();
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_employees', 'parent_id')->where('active', 1);
    }


    public function scopeSearchByFullName($query, $searchTerm)
    {
        $searchTerm = strtolower($searchTerm);
        return $query->where(function ($query) use ($searchTerm) {
            $query->whereRaw('LOWER(name) LIKE ?', ['%' . $searchTerm . '%'])
                ->orWhereRaw('LOWER(middle_name) LIKE ?', ['%' . $searchTerm . '%'])
                ->orWhereRaw('LOWER(surname) LIKE ?', ['%' . $searchTerm . '%']);
        });
    }

    public function scopeSearchByPinfOrPhone($query, $searchTerm)
    {
        return $query->where(function ($query) use ($searchTerm) {
            $query->where('pinfl', 'LIKE', '%' . $searchTerm . '%')
                ->orWhere('phone', 'LIKE', '%' . $searchTerm . '%');
        });
    }

    public function scopeSearchAll($query, $searchTerm)
    {
        $searchTerm = strtolower($searchTerm);
        return $query->where(function ($query) use ($searchTerm) {
            $query->whereRaw('LOWER(name) LIKE ?', ['%' . $searchTerm . '%'])
                ->orWhereRaw('LOWER(middle_name) LIKE ?', ['%' . $searchTerm . '%'])
                ->orWhereRaw('LOWER(surname) LIKE ?', ['%' . $searchTerm . '%'])
                ->orWhereRaw("pinfl LIKE ?", ['%' . $searchTerm . '%'])
                ->orWhereRaw("phone LIKE ?", ['%' . $searchTerm . '%']);
        });
    }

    public function getFullNameAttribute()
    {
        return  isset($this->name) ? ucwords("{$this->surname} {$this->name} {$this->middle_name}") : null;
    }

}
