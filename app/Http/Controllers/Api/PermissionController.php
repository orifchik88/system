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
        try {
            if (request('id'))
            {
                $role = Role::findOrFail(request('id'));

                $roles =  Role::query()->whereIn('id', $role->children)->paginate(request('per_page', 15));

                return $this->sendSuccess(RoleResource::collection($roles), 'Roles', pagination($roles));
            }
            $roles = Role::query()->paginate(request('per_page', 15));
            return $this->sendSuccess(RoleResource::collection($roles), 'Roles', pagination($roles));
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage());
        }

    }
}
