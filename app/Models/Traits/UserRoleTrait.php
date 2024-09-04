<?php

namespace App\Models\Traits;

trait UserRoleTrait
{
    public function isKadr(): bool
    {
        return $this->roles()->where('role_id', 1)->exists();
    }

    public function isRegionKadr(): bool
    {
        return $this->roles()->where('role_id', 2)->exists();
    }

    public function inspector(): bool
    {
        return $this->roles()->where('role_id', 3)->exists();
    }
    public function register(): bool
    {
        return $this->roles()->where('role_id', 4)->exists();
    }

    public function internalControl(): bool
    {
        return $this->roles()->where('role_id', 5)->exists();
    }

    public function technicControl(): bool
    {
        return $this->roles()->where('role_id', 6)->exists();
    }

    public function authorControl(): bool
    {
        return $this->roles()->where('role_id', 7)->exists();
    }

    public function customer(): bool
    {
        return  $this->roles()->where('role_id', 8)->exists();
    }

    public function designer(): bool
    {
        return $this->roles()->where('role_id', 9)->exists();
    }

    public function builder(): bool
    {
        return $this->roles()->where('role_id', 10)->exists();
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
