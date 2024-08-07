<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\RoleRequest;
use App\Http\Resources\RoleResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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

    public function roles(Request $request): JsonResponse
    {
        if ($request::input('id'))
        {
            $role = Role::findOrFail($request::input('id'));
            return $this->sendSuccess(RoleResource::make($role), 'Role');
        }
        $roles = Role::query()->paginate(request::input('page_size', 10));
        return $this->sendSuccess(RoleResource::collection($roles), 'All Roles', pagination($roles));
    }

    public function create(RoleRequest $request): JsonResponse
    {
        $role = Role::create(['name' => $request->input('name'), 'guard_name' => 'web', 'client_type_id' => $request->input('client_type_id')]);

        $permissions = Permission::whereIn('id', $request->input('permissions'))->pluck('name')->toArray();

        $role->syncPermissions($permissions);

        return $this->sendSuccess(new RoleResource($role, true), 'Role created');
    }
}
