<?php

namespace App\Models\Traits;

trait UserRoleTrait
{
//    public function isInspector($roleId): bool
//    {
//        return ;
//    }

    public function isKadr(): bool
    {
        return $this->roles()->where('role_id', 1)->exists();
    }

    public function isRegister($roleId): bool
    {
        return $this->hasPermissionTo('is_register');
    }


    public function scopeSearchByFullName($query, $searchTerm)
    {
        $searchTerm = strtolower($searchTerm);
        return $query->whereRaw('LOWER(name) LIKE ?', ['%' . $searchTerm . '%'])
            ->orWhereRaw('LOWER(middle_name) LIKE ?', ['%' . $searchTerm . '%'])
            ->orWhereRaw('LOWER(surname) LIKE ?', ['%' . $searchTerm . '%']);
    }

    public function scopeSearchByPinfOrPhone($query, $searchTerm)
    {
        return $query->where('pinfl LIKE ?', ['%' . $searchTerm . '%'])
            ->orWhereRaw('phone LIKE ?', ['%' . $searchTerm . '%']);
    }
    public function getFullNameAttribute()
    {
        return ucwords("{$this->surname} {$this->name} {$this->middle_name}");
    }

}
