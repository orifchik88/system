<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\RoleRequest;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends BaseController
{
    public function permissions(): JsonResponse
    {
        $permissions = Permission::all();

        $groupedPermissions = $permissions->groupBy('group_name')->map(function ($group) {
            return [
                'group_name' => $group->first()->group_name,
                'permissions' => $group->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                    ];
                })->values()->all(),
            ];
        })->values()->all();

        return $this->sendSuccess($groupedPermissions, 'All Permissions');
    }

    public function roles(): JsonResponse
    {
        if (request('id'))
        {
            $role = Role::findOrFail(request('id'));
            return $this->sendSuccess(RoleResource::make($role), 'Role');
        }
        if (request('type'))
        {
            $roles = Role::query()->where('type', request('type'))->paginate(request('per_page', 10));
            return $this->sendSuccess(RoleResource::collection($roles), 'Roles by type', pagination($roles));
        }
        $roles = Role::query()->paginate(request::input('page_size', 10));
        return $this->sendSuccess(RoleResource::collection($roles), 'All Roles', pagination($roles));
    }
}
