<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct()
    {
//        $this->middleware(['permission:role list'])->only(['index']);
//        $this->middleware(['permission:create role'])->only(['create']);
//        $this->middleware(['permission:edit role'])->only(['edit']);
//        $this->middleware(['permission:delete role'])->only(['destroy']);
    }

    public function index()
    {
        $roles = Role::with('permissions')->latest()->get();
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::all();
        // $permission_groups = User::getPermissionGroup();

        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name'
        ]);


        $role = Role::create(['name' => $request->input('name'), 'guard_name' => 'web']);

//        dd($request->input('permissions'));
//        $role->syncPermissions($request->input('permissions'));

        $permissions = Permission::whereIn('id', $request->input('permissions'))->pluck('name')->toArray();

        $role->syncPermissions($permissions);

        session()->flash('success', 'Role Created!');
        return redirect()->route('roles.index');
    }

    public function show(Role $role)
    {

    }

    public function edit($id)
    {
        $permissions = Permission::all();
        $role = Role::with('permissions')->find($id);
        $data = $role->permissions()->pluck('id')->toArray();

        return view('roles.edit', compact(['permissions', 'role', 'data']));
    }

    public function update(Request $request, Role $role)
    {
        // abort_if(!userCan('role.update'), 403);
        $request->validate([
            'name' => "required|unique:roles,name, $role->id"
        ]);

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions);

        session()->flash('success', 'Role has been updated successfully!');
        return back();
    }


    public function destroy(Role $role)
    {
        $role->delete();
        session()->flash('success', 'Role has been deleted successfully deleted!');
        return back();
    }
}
