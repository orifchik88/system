<?php

namespace App\Models\Traits;

trait UserRoleTrait
{
    public function isInspector(): bool
    {
        return $this->hasPermissionTo('is_inspector');
    }

    public function isRegister(): bool
    {
        return $this->hasPermissionTo('is_register');
    }

    public function isTechnic(): bool
    {
        return $this->hasPermissionTo('is_technical');
    }

    public function isAuthor(): bool
    {
        return $this->hasPermissionTo('is_author');
    }

    public function isDesigner(): bool
    {
        return $this->hasPermissionTo('is_designer');
    }
}